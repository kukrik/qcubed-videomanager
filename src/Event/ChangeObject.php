<?php

    namespace QCubed\Plugin\Event;

    use QCubed\Event\EventBase;

    /**
     * Class ChangeObject
     *
     * Detects a save an event that occurs when a cropped image is sent to save and can optionally trigger another event on other objects.
     *
     */

    class ChangeObject extends EventBase {

        const string EVENT_NAME = 'changeobject';
        const string JS_RETURN_PARAM = 'ui';
    }
