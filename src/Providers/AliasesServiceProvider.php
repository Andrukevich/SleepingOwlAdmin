<?php

namespace SleepingOwl\Admin\Providers;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use KodiCMS\Assets\Contracts\MetaInterface;
use KodiCMS\Assets\Contracts\PackageManagerInterface;
use SleepingOwl\Admin\Contracts\Display\DisplayColumnEditableFactoryInterface;
use SleepingOwl\Admin\Contracts\Display\DisplayColumnFilterFactoryInterface;
use SleepingOwl\Admin\Contracts\Display\DisplayColumnFactoryInterface;
use SleepingOwl\Admin\Contracts\Display\DisplayFactoryInterface;
use SleepingOwl\Admin\Contracts\Display\DisplayFilterFactoryInterface;
use SleepingOwl\Admin\Contracts\Form\FormElementFactoryInterface;
use SleepingOwl\Admin\Contracts\Form\FormFactoryInterface;
use SleepingOwl\Admin\Display;
use SleepingOwl\Admin\Factories\DisplayColumnEditableFactory;
use SleepingOwl\Admin\Factories\DisplayColumnFilterFactory;
use SleepingOwl\Admin\Factories\DisplayColumnFactory;
use SleepingOwl\Admin\Factories\DisplayFactory;
use SleepingOwl\Admin\Factories\DisplayFilterFactory;
use SleepingOwl\Admin\Factories\FormElementFactory;
use SleepingOwl\Admin\Factories\FormFactory;
use SleepingOwl\Admin\Form;
use SleepingOwl\Admin\PackageManager;

class AliasesServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(PackageManager::class, function (Container $app) {
            return new PackageManager($app->make(PackageManagerInterface::class), $app->make(MetaInterface::class));
        });

        $this->registerColumns();
        $this->registerColumnEditable();
        $this->registerColumnFilters();
        $this->registerDisplays();
        $this->registerForms();
        $this->registerFormElements();
        $this->registerFilters();
    }

    protected function registerColumnFilters()
    {
        $alias = $this->app->make(DisplayColumnFilterFactory::class)->register([
            'text'   => Display\Column\Filter\Text::class,
            'date'   => Display\Column\Filter\Date::class,
            'range'  => Display\Column\Filter\Range::class,
            'select' => Display\Column\Filter\Select::class,
        ]);

        $this->app->singleton('sleeping_owl.column_filter', function () use ($alias) {
            return $alias;
        });
        $this->app->alias('sleeping_owl.column_filter', DisplayColumnFilterFactoryInterface::class);
    }

    protected function registerDisplays()
    {
        $alias = $this->app->make(DisplayFactory::class)->register([
            'datatables'      => Display\DisplayDatatables::class,
            'datatablesAsync' => Display\DisplayDatatablesAsync::class,
            'tab'             => Display\DisplayTab::class,
            'tabbed'          => Display\DisplayTabbed::class,
            'table'           => Display\DisplayTable::class,
            'tree'            => Display\DisplayTree::class,
        ]);

        $this->app->singleton('sleeping_owl.display', function () use ($alias) {
            return $alias;
        });
        $this->app->alias('sleeping_owl.display', DisplayFactoryInterface::class);
    }

    protected function registerColumns()
    {
        $alias = $this->app->make(DisplayColumnFactory::class)->register([
            'action'      => Display\Column\Action::class,
            'checkbox'    => Display\Column\Checkbox::class,
            'control'     => Display\Column\Control::class,
            'count'       => Display\Column\Count::class,
            'custom'      => Display\Column\Custom::class,
            'datetime'    => Display\Column\DateTime::class,
            'filter'      => Display\Column\Filter::class,
            'image'       => Display\Column\Image::class,
            'lists'       => Display\Column\Lists::class,
            'order'       => Display\Column\Order::class,
            'text'        => Display\Column\Text::class,
            'link'        => Display\Column\Link::class,
            'relatedLink' => Display\Column\RelatedLink::class,
            'email'       => Display\Column\Email::class,
            'treeControl' => Display\Column\TreeControl::class,
            'url'         => Display\Column\Url::class,
        ]);

        $this->app->singleton('sleeping_owl.table.column', function () use ($alias) {
            return $alias;
        });
        $this->app->alias('sleeping_owl.table.column', DisplayColumnFactoryInterface::class);
    }

    protected function registerColumnEditable()
    {
        $alias = $this->app->make(DisplayColumnEditableFactory::class)->register([
            'checkbox'    => Display\Column\Editable\Checkbox::class,
        ]);

        $this->app->singleton('sleeping_owl.table.column.editable', function () use ($alias) {
            return $alias;
        });
        $this->app->alias('sleeping_owl.table.column.editable', DisplayColumnEditableFactoryInterface::class);
    }

    protected function registerFormElements()
    {
        $alias = $this->app->make(FormElementFactory::class)->register([
            'columns'     => Form\Columns\Columns::class,
            'text'        => Form\Element\Text::class,
            'time'        => Form\Element\Time::class,
            'date'        => Form\Element\Date::class,
            'timestamp'   => Form\Element\Timestamp::class,
            'textaddon'   => Form\Element\TextAddon::class,
            'select'      => Form\Element\Select::class,
            'multiselect' => Form\Element\MultiSelect::class,
            'hidden'      => Form\Element\Hidden::class,
            'checkbox'    => Form\Element\Checkbox::class,
            'ckeditor'    => Form\Element\CKEditor::class,
            'custom'      => Form\Element\Custom::class,
            'password'    => Form\Element\Password::class,
            'textarea'    => Form\Element\Textarea::class,
            'view'        => Form\Element\View::class,
            'image'       => Form\Element\Image::class,
            'images'      => Form\Element\Images::class,
            'file'        => Form\Element\File::class,
            'radio'       => Form\Element\Radio::class,
            'wysiwyg'     => Form\Element\Wysiwyg::class,
            'upload'      => Form\Element\Upload::class,
            'html'        => Form\Element\Html::class,
        ]);

        $this->app->singleton('sleeping_owl.form.element', function () use ($alias) {
            return $alias;
        });
        $this->app->alias('sleeping_owl.form.element', FormElementFactoryInterface::class);
    }

    protected function registerForms()
    {
        $alias = $this->app->make(FormFactory::class)->register([
            'form' => Form\FormDefault::class,
            'elements' => Form\FormElements::class,
            'tabbed' => Form\FormTabbed::class,
            'panel' => Form\FormPanel::class,
        ]);

        $this->app->singleton('sleeping_owl.form', function () use ($alias) {
            return $alias;
        });
        $this->app->alias('sleeping_owl.form', FormFactoryInterface::class);
    }

    protected function registerFilters()
    {
        $alias = $this->app->make(DisplayFilterFactory::class)->register([
            'field'   => Display\Filter\FilterField::class,
            'scope'   => Display\Filter\FilterScope::class,
            'custom'  => Display\Filter\FilterCustom::class,
            'related' => Display\Filter\FilterRelated::class,
        ]);

        $this->app->singleton('sleeping_owl.display.filter', function () use ($alias) {
            return $alias;
        });
        $this->app->alias('sleeping_owl.display.filter', DisplayFilterFactoryInterface::class);
    }
}
