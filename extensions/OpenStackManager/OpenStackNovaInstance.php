<?php

# TODO: Make this an abstract class, and make the EC2 API a subclass
class OpenStackNovaInstance {

	var $instance;

	function __construct( $apiInstanceResponse ) {
		$this->instance = $apiInstanceResponse;
	}

	function getReservationId() {
		return $this->instance->reservationId;
	}

	function getInstanceId() {
		return $this->instance->instancesSet->item->instanceId;
	}

	function getInstanceState() {
		return $this->instance->instancesSet->item->instanceState->name;
	}

	function getInstanceType() {
		return $this->instance->instancesSet->item->instanceType;
	}

	function getImageId() {
		return $this->instance->instancesSet->item->imageId;
	}

	function getKeyName() {
		return $this->instance->instancesSet->item->keyName;
	}

	function getOwner() {
		return $this->instance->ownerId;
	}

}
