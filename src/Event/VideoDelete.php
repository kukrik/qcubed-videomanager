<?php

    namespace QCubed\Plugin\Event;

    use QCubed\Event\EventBase;

    /**
     * Class VideoDelete
     *
     * Captures the delete event that occurs after the popup is closed.
     *
     */

    class VideoDelete extends EventBase {

        const string EVENT_NAME = 'videodelete';
        const string JS_RETURN_PARAM = 'ui';
    }
