<?php

return [
	\Gcore\Event\WorkOrderFinishedEvent::class => [
		\Gcore\Event\SendWelcomeMessageToCustomerListener::class,
		\Gcore\Event\CreateProviderUserForCustomerListener::class,
	],
];