<?php

namespace Vildanbina\LivewireTabs;

use Closure;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Vildanbina\LivewireTabs\Components\Tab;
use Vildanbina\LivewireTabs\Concerns\HasHooks;
use Vildanbina\LivewireTabs\Concerns\HasState;
use Vildanbina\LivewireTabs\Contracts\TabForm;

abstract class TabsComponent extends Component implements TabForm
{
    use HasHooks;
    use HasState;

    public bool $saveTabState = true;
    public null|array|Model $model = null;
    public int $activeTab = 0;
    public array $tabs = [];
    protected array $cachedTabs = [];

    public function __construct($id = null)
    {
        parent::__construct($id);

        if ($this->saveTabState) {
            $this->queryString[] = 'activeTab';
        }
    }

    public function setTab($tab): void
    {
        $this->callHook('beforeSetTab', $this->activeTab, $tab);

        $this->getCurrentTab()->callHook('onTabOut');

        $this->activeTab = $tab;

        $this->getCurrentTab()->callHook('onTabIn');

        $this->callHook('afterSetTab', $this->activeTab, $tab);
    }

    public function getCurrentTab(): ?Tab
    {
        return $this->getTabInstance($this->activeTab);
    }

    public function getTabInstance($tab): ?Tab
    {
        if (($tabInstance = data_get($this->tabClasses(), $tab)) && !$tabInstance instanceof Tab) {
            throw new Exception(get_class($tabInstance) . ' must bee ' . Tab::class . ' instance');
        }

        return $tabInstance;
    }

    protected function tabClasses(null|Closure $callback = null): array
    {
        if (filled($this->cachedTabs)) {
            return collect($this->cachedTabs)
                ->each(fn(Tab $tab, $index) => value($callback, $tab, $index))
                ->toArray();
        }

        if (filled($this->tabs())) {
            $this->cachedTabs = collect($this->tabs())
                ->map(function ($tab, $index) use ($callback) {
                    if (class_exists($tab) && is_subclass_of($tab, Tab::class)) {
                        $tabInstance = $tab::make($this);

                        if (is_null($tabInstance->getOrder())) {
                            $tabInstance->setOrder($index);
                        }

                        return $tabInstance;
                    }
                    return null;
                })
                ->filter()
                ->sortBy('order')
                ->values()
                ->toArray();

            if ($callback instanceof Closure) {
                return $this->tabClasses($callback);
            }
        }

        return $this->cachedTabs;
    }

    public function tabs(): array
    {
        if (property_exists($this, 'tabs')) {
            return $this->tabs;
        }

        return [];
    }

    public function mount()
    {
        $this->callHook('beforeMount', ...func_get_args());

        if (method_exists($this, 'model')) {
            $this->model = $this->model();
        }

        $this->tabClasses(function (Tab $tab) {
            if (method_exists($tab, 'mount')) {
                $tab->mount();
            }
        });

        $this->callHook('afterMount', ...func_get_args());
    }

    public function getModel(): ?Model
    {
        return $this->model;
    }

    public function updated($name, $value): void
    {
        $this->callHooksTab('updated', $name, $value);
    }

    private function callHooksTab($hook, $name, $value): void
    {
        $tabInstance = $this->getCurrentTab();
        $name        = str($name);

        $propertyName     = $name->studly()->before('.');
        $keyAfterFirstDot = $name->contains('.') ? $name->after('.')->__toString() : null;
        $keyAfterLastDot  = $name->contains('.') ? $name->afterLast('.')->__toString() : null;

        $beforeMethod = $hook . $propertyName;

        $beforeNestedMethod = $name->contains('.')
            ? $hook . $name->replace('.', '_')->studly()
            : false;

        $tabInstance->callHook($beforeMethod, $value, $keyAfterFirstDot);

        if ($beforeNestedMethod) {
            $tabInstance->callHook($beforeNestedMethod, $value, $keyAfterLastDot);
        }
    }

    public function tabFooter()
    {
        return view('livewire-tabs::tabs-footer');
    }

    public function updating($name, $value): void
    {
        $this->callHooksTab('updating', $name, $value);
    }

    public function save(): void
    {
        $this->callHook('beforeValidate');

        $this->tabsValidation();

        $this->callHook('afterValidate');

        $state = $this->mutateStateBeforeSave($this->getState());

        $this->callHook('beforeSave');

        $this->tabClasses(function (Tab $tabInstance) use ($state) {
            if (method_exists($tabInstance, 'save')) {
                $tabInstance->save($state);
            }
        });

        $this->callHook('afterSave');
    }

    protected function tabsValidation($tab = null): void
    {
        [$rules, $messages, $attributes] = [[], [], []];
        $tab = $tab ?? max(array_keys($this->tabs()));

        $this->tabClasses(function (Tab $tabInstance) use ($tab, &$rules, &$messages, &$attributes) {
            if (method_exists($tabInstance, 'validate') && $tabInstance->getOrder() <= $tab) {
                $tabValidate = $tabInstance->validate();

                $rules      = array_merge($rules, $tabValidate[0] ?? []);
                $messages   = array_merge($messages, $tabValidate[1] ?? []);
                $attributes = array_merge($attributes, $tabValidate[2] ?? []);
            }
        });

        if (filled($rules)) {
            $this->validate($rules, $messages, $attributes);
        }
    }

    public function mutateStateBeforeSave(array $state = []): array
    {
        return $state;
    }

    public function render(): View
    {
        return view('livewire-tabs::tab-container', [
            'tabInstances' => $this->tabClasses(),
        ]);
    }
}
