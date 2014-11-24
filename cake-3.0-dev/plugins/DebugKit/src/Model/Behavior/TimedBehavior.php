<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         DebugKit 1.3
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace DebugKit\Model\Behavior;

use Cake\Event\Event;
use Cake\ORM\Behavior;
use DebugKit\DebugTimer;

/**
 * Class TimedBehavior
 */
class TimedBehavior extends Behavior {

/**
 * beforeFind, starts a timer for a find operation.
 *
 * @param \Cake\Event\Event $event The beforeFind event
 * @param \Cake\ORM\Query $query Query
 * @return bool true
 */
	public function beforeFind(Event $event, $query) {
		$alias = $event->subject()->alias();
		DebugTimer::start($alias . '_find', $alias . '->find()');
		return $query->formatResults(function ($results) use ($alias) {
			DebugTimer::stop($alias . '_find');
			return $results;
		});
	}

/**
 * beforeSave, starts a time before a save is initiated.
 *
 * @param Cake\Event\Event $event The beforeSave event
 * @return void
 */
	public function beforeSave(Event $event) {
		$alias = $event->subject()->alias();
		DebugTimer::start($alias . '_save', $alias . '->save()');
	}

/**
 * afterSave, stop the timer started from a save.
 *
 * @param Cake\Event\Event $event The afterSave event
 * @return void
 */
	public function afterSave(Event $event) {
		$alias = $event->subject()->alias();
		DebugTimer::stop($alias . '_save');
	}

}
