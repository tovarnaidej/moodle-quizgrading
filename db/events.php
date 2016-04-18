<?php 

$observers = array(
 
    array(
        'eventname'   => '\mod_quiz\event\attempt_submitted',
        'callback'    => 'mod_quizgrading_quiz_observer::observe_one',
    ),
    array(
        'eventname'   => '*',
        'callback'    => 'mod_quizgrading_quiz_observer::observe_all',
        'includefile' => null,
        'internal'    => true,
        'priority'    => 9999,
    )
);



?>