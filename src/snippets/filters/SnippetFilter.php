<?php

namespace rock\snippets\filters;


use rock\components\Behavior;
use rock\events\Event;
use rock\filters\FilterInterface;
use rock\helpers\Instance;
use rock\request\Request;
use rock\response\Response;
use rock\snippets\Snippet;
use rock\snippets\SnippetEvent;

class SnippetFilter extends Behavior implements FilterInterface
{
    /**
     * @var Request the current request. If not set, the `request` application component will be used.
     */
    public $request = 'request';
    /**
     * @var Response the response to be sent. If not set, the `response` application component will be used.
     */
    public $response;
    /**
     * Success as callable, when using filter.
     *
     * ```php
     * [new Object, 'method']
     * ['Object', 'staticMethod']
     * closure
     * ```
     * @var array
     */
    public $success;
    /**
     * Fail as callable, when using filter.
     *
     * ```php
     * [new Object, 'method']
     * ['Object', 'staticMethod']
     * closure
     * ```
     * @var array
     */
    public $fail;
    public $data;
    protected $event;

    public function init()
    {
        parent::init();
        $this->request = Instance::ensure($this->request, '\rock\request\Request');
    }

    public function events()
    {
        return [
            Snippet::EVENT_BEFORE_SNIPPET => 'beforeFilter',
            Snippet::EVENT_AFTER_SNIPPET => 'afterFilter'
        ];
    }

    /**
     * @inheritdoc
     */
    public function attach($owner)
    {
        $this->owner = $owner;
        foreach (array_keys($this->events(), 'beforeFilter', true) as $event) {
            $owner->on($event, [$this, 'beforeFilter']);
        }
    }

    /**
     * @param Event|SnippetEvent $event
     */
    public function beforeFilter(Event $event)
    {
        $this->event = $event;
        $this->event->isValid = $this->before();
        if ($event->isValid) {
            // call afterFilter only if beforeFilter succeeds
            // beforeFilter and afterFilter should be properly nested
            $this->owner->on(Snippet::EVENT_AFTER_SNIPPET, [$this, 'afterFilter'], null, false);
            $this->callback($this->success);
        } else {
            $event->handled = true;
            $this->callback($this->fail);
        }
    }

    /**
     * @param Event|SnippetEvent $event
     */
    public function afterFilter(Event $event)
    {
        $this->event = $event;
        $event->result = $this->after($this->event->result);
        $this->owner->off(Snippet::EVENT_AFTER_SNIPPET, [$this, 'afterFilter']);
    }

    /**
     * This method is invoked right before an snippet is to be executed (after all possible filters.)
     * @return boolean whether the action should continue to be executed.
     */
    public function before()
    {
        return true;
    }

    /**
     * This method is invoked right after an snippet is executed.
     * You may override this method to do some postprocessing for the snippet.
     * @param mixed $result the snippet execution result
     * @return mixed the processed snippet result.
     */
    public function after($result)
    {
        return $result;
    }

    protected function callback(callable $handler = null)
    {
        if (!isset($handler)) {
            return;
        }
        call_user_func($handler, $this);
    }
}