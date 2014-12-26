<?php
/* @var $this OperationLogController */
/* @var $data OperationLog */
?>

<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('id')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->id), array('view', 'id'=>$data->id)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('customer_id')); ?>:</b>
	<?php echo CHtml::encode($data->customer_id); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('actions')); ?>:</b>
	<?php echo CHtml::encode($data->actions); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('gets')); ?>:</b>
	<?php echo CHtml::encode($data->gets); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('posts')); ?>:</b>
	<?php echo CHtml::encode($data->posts); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('create_time')); ?>:</b>
	<?php echo CHtml::encode($data->create_time); ?>
	<br />


</div>