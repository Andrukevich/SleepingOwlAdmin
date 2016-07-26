<?php

namespace SleepingOwl\Admin\Form;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Support\Collection;
use KodiComponents\Support\HtmlAttributes;
use Request;
use SleepingOwl\Admin\Contracts\DisplayInterface;
use SleepingOwl\Admin\Contracts\FormButtonsInterface;
use SleepingOwl\Admin\Contracts\FormElementInterface;
use SleepingOwl\Admin\Contracts\FormInterface;
use SleepingOwl\Admin\Contracts\ModelConfigurationInterface;
use SleepingOwl\Admin\Contracts\RepositoryInterface;
use SleepingOwl\Admin\Form\Element\Upload;
use Validator;

class FormDefault extends FormElements implements DisplayInterface, FormInterface
{
    use HtmlAttributes;

    /**
     * View to render.
     * @var string|\Illuminate\View\View
     */
    protected $view = 'form.default';

    /**
     * Form related class.
     * @var string
     */
    protected $class;

    /**
     * @var FormButtons
     */
    protected $buttons;

    /**
     * Form related repository.
     * @var RepositoryInterface
     */
    protected $repository;


    /**
     * Currently loaded model id.
     * @var int
     */
    protected $id;

    /**
     * Is form already initialized?
     * @var bool
     */
    protected $initialized = false;

    /**
     * FormDefault constructor.
     *
     * @param array $elements
     */
    public function __construct(array $elements = [])
    {
        parent::__construct($elements);

        $this->setButtons(
            app(FormButtonsInterface::class)
        );

        $this->initializePackage();
    }

    /**
     * @return boolean
     */
    public function isInitialized()
    {
        return $this->initialized;
    }

    /**
     * Initialize form.
     */
    public function initialize()
    {
        if ($this->initialized) {
            return;
        }

        $this->initialized = true;
        $this->repository = app(RepositoryInterface::class, [$this->class]);

        $this->setModel(app($this->class));

        parent::initialize();

        $this->getElements()->each(function ($element) {
            if ($element instanceof Upload and ! $this->hasHtmlAttribute('enctype')) {
                $this->setHtmlAttribute('enctype', 'multipart/form-data');
            }
        });

        $this->setHtmlAttribute('method', 'POST');

        $this->getButtons()->setModelConfiguration(
            $this->getModelConfiguration()
        );

        $this->includePackage();
    }

    /**
     * @return FormButtons
     */
    public function getButtons()
    {
        return $this->buttons;
    }

    /**
     * @param FormButtonsInterface $buttons
     *
     * @return $this
     */
    public function setButtons(FormButtonsInterface $buttons)
    {
        if ($this->isInitialized()) {
            $buttons->setModelConfiguration(
                $this->getModelConfiguration()
            );
        }

        $this->buttons = $buttons;

        return $this;
    }

    /**
     * @return RepositoryInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return string|\Illuminate\View\View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param \Illuminate\View\View|string $view
     *
     * @return $this
     */
    public function setView($view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * @param string $action
     *
     * @return $this
     */
    public function setAction($action)
    {
        $this->setHtmlAttribute('action', $action);

        return $this;
    }

    /**
     * @return string
     */
    public function getModelClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     *
     * @return $this
     */
    public function setModelClass($class)
    {
        if (is_null($this->class)) {
            $this->class = $class;
        }

        return $this;
    }

    /**
     * @deprecated 4.5.0
     * @see getElements()
     *
     * @return Collection[]
     */
    public function getItems()
    {
        return $this->getElements();
    }

    /**
     * @deprecated 4.5.0
     * @see setElements()
     *
     * @param array|FormElementInterface $items
     *
     * @return $this
     */
    public function setItems($items)
    {
        if (! is_array($items)) {
            $items = func_get_args();
        }

        return $this->setElements($items);
    }

    /**
     * @deprecated 4.5.0
     * @see addElement()
     *
     * @param FormElementInterface $item
     *
     * @return $this
     */
    public function addItem($item)
    {
        return $this->addElement($item);
    }

    /**
     * Set currently loaded model id.
     *
     * @param int $id
     */
    public function setId($id)
    {
        if (is_null($this->id) and ! is_null($id) and ($model = $this->getRepository()->find($id))) {
            $this->setModel($model);
        }
    }

    /**
     * Get related form model configuration.
     * @return ModelConfigurationInterface
     */
    public function getModelConfiguration()
    {
        return app('sleeping_owl')->getModel($this->class);
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param Model $model
     *
     * @return $this
     */
    public function setModel(Model $model)
    {
        $this->id = $model->getKey();

        parent::setModel($model);

        $this->getButtons()->setModel($this->getModel());

        return $this;
    }

    /**
     * Save instance.
     */
    public function saveForm()
    {
        parent::save();

        $this->saveBelongsToRelations();

        $this->getModel()->save();

        $this->saveHasOneRelations();

        parent::afterSave();
    }

    protected function saveBelongsToRelations()
    {
        $model = $this->getModel();

        foreach ($model->getRelations() as $name => $related) {
            if (($relation = $model->{$name}()) instanceof BelongsTo && !is_null($related)) {
                $related->save();
                $model->{$name}()->associate($related);
            }
        }
    }

    protected function saveHasOneRelations()
    {
        $model = $this->getModel();

        foreach ($model->getRelations() as $name => $related) {
            if (($relation = $model->{$name}()) instanceof HasOneOrMany && !is_null($related)) {
                if (is_array($related) || $related instanceof \Traversable || $related instanceof Collection) {
                    $relation->saveMany($related);
                } else {
                    $relation->save($related);
                }
            }
        }
    }

    /**
     * @return \Illuminate\Contracts\Validation\Validator|null
     */
    public function validateForm()
    {
        $data = Request::all();

        $verifier = app('validation.presence');
        $verifier->setConnection($this->getModel()->getConnectionName());

        $validator = Validator::make(
            $data,
            $this->getValidationRules(),
            $this->getValidationMessages(),
            $this->getValidationLabels()
        );

        $validator->setPresenceVerifier($verifier);

        if ($validator->fails()) {
            return $validator;
        }

        return true;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'items' => $this->getElements(),
            'instance' => $this->getModel(),
            'attributes' => $this->htmlAttributesToString(),
            'buttons' => $this->getButtons(),
            'backUrl' => session('_redirectBack', \URL::previous()),
        ];
    }

    /**
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function render()
    {
        return app('sleeping_owl.template')->view($this->getView(), $this->toArray());
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->render();
    }
}
