[![Latest Stable Version](http://poser.pugx.org/vildanbina/livewire-tabs/v)](https://packagist.org/packages/vildanbina/livewire-tabs)
[![Total Downloads](http://poser.pugx.org/vildanbina/livewire-tabs/downloads)](https://packagist.org/packages/vildanbina/livewire-tabs)
[![Latest Unstable Version](http://poser.pugx.org/vildanbina/livewire-tabs/v/unstable)](https://packagist.org/packages/vildanbina/livewire-tabs)
[![License](http://poser.pugx.org/vildanbina/livewire-tabs/license)](https://packagist.org/packages/vildanbina/livewire-tabs)
[![PHP Version Require](http://poser.pugx.org/vildanbina/livewire-tabs/require/php)](https://packagist.org/packages/vildanbina/livewire-tabs)

A dynamic Laravel Livewire component for tab forms.

![Tabs form](https://user-images.githubusercontent.com/51203303/155887948-c5423998-c993-437a-9947-1e94077c750c.png)

## Installation

You can install the package via composer:

``` bash
composer require vildanbina/livewire-tabs
```

## TailwindCSS

The base modal is made with TailwindCSS. If you use a different CSS framework I recommend that you publish the modal template and change the markup to include the required classes for your CSS framework.

```shell
php artisan vendor:publish --tag=livewire-tabs-views
```

## Usage

### Creating a Tab Container

You can create livewire component `php artisan make:livewire UserTab` to make the initial Livewire component. Open your component class and make sure it extends the `TabsComponent` class:

```php
<?php

namespace App\Http\Livewire;

use Vildanbina\LivewireTabs\TabsComponent;
use App\Models\User;

class UserTab extends TabsComponent
{
    // My custom class property
    public $userId;
    
    /*
     * Will return App\Models\User instance or will create empty User (based on $userId parameter) 
     */
    public function model()
    {
        return User::findOrNew($this->userId);
    }
}
```

When you need to display tabs form, based on above example we need to pass `$userId` value and to display tabs form:

```html 
<livewire:user-tabs user-id="3"/>
```

Or when you want to create new user, let blank `user-id` attribute, or don't put that.

When you want to have current tab instance. You can use:

```php
$tabsFormInstance->getCurrentTab();
```

When you want to go to specific tab. You can use:

```php
$tabsFormInstance->setTab($tab);
``` 

You can customize tab footer buttons, create some view and put that view to method:

```php
public function tabFooter()
{
    return view('livewire-tabs::tabs-footer');
}
``` 

### Creating a Tab Item

You can create tabs form tab. Open or create your tab class (at `App\Tabs` folder) and make sure it extends the `Tab` class:

```php
<?php

namespace App\Tabs;

use Vildanbina\LivewireTabs\Components\Tab;
use Illuminate\Validation\Rule;

class General extends Tab
{
    // Tab view located at resources/views/tabs/general.blade.php 
    protected string $view = 'tabs.general';

    /*
     * Initialize tab fields
     */
    public function mount()
    {
        $this->mergeState([
            'name'                  => $this->model->name,
            'email'                 => $this->model->email,
        ]);
    }
    
    /*
    * Tab icon 
    */
    public function icon()
    {
        return view('icons.home');
    }

    /*
     * When Tabs Form has submitted
     */
    public function save($state)
    {
        $user = $this->model;

        $user->name     = $state['name'];
        $user->email    = $state['email'];
        
        $user->save();
    }

    /*
     * Tab Validation
     */
    public function validate()
    {
        return [
            [
                'state.name'     => ['required', Rule::unique('users', 'name')->ignoreModel($this->model)],
                'state.email'    => ['required', Rule::unique('users', 'email')->ignoreModel($this->model)],
            ],
            [
                'state.name'     => __('Name'),
                'state.email'    => __('Email'),
            ],
        ];
    }

    /*
     * Tab Title
     */
    public function title(): string
    {
        return __('General');
    }
}
```

In Tab class, you can use livewire hooks example:

```php
use Vildanbina\LivewireTabs\Components\Tab;

class General extends Tab
{
    public function onTabIn($name, $value)
    {
        // Something you want
    }

    public function onTabOut($name, $value)
    {
        // Something you want
    }

    public function updating($name, $value)
    {
        // Something you want
    }

    public function updatingState($name, $value)
    {
        // Something you want
    }
    
    public function updated($name, $value)
    {
        // Something you want
    }

    public function updatedState($name, $value)
    {
        // Something you want
    }
}
```

Each tab need to have view, you can pass view path in `$view` property.

After create tab class, you need to put that tab to tabs form:

```php
<?php

namespace App\Http\Livewire;

use App\Tabs\General;
use Vildanbina\LivewireTabs\TabsComponent;

class UserTab extends TabsComponent
{
    public array $tabs = [
        General::class,
        // Other tabs...
    ];
   
    ...
}
```

## Building Tailwind CSS for production

Because some classes are dynamically build and to compile js you should add some classes to the purge safelist so your `tailwind.config.js` should look something like this:

```js
module.exports = {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",

        "./vendor/vildanbina/livewire-tabs/resources/views/*.blade.php",
    ]
};

```

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please e-mail vildanbina@gmail.com to report any security vulnerabilities instead of the issue tracker.

## Credits

- [Vildan Bina](https://github.com/vildanbina)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
