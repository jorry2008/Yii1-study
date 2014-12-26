<?php
/* @var $this OperationLogController */
/* @var $model OperationLog */

$this->breadcrumbs=array(
	'Operation Logs'=>array('index'),
	$model->id,
);

$this->menu=array(
	array('label'=>'List OperationLog', 'url'=>array('index')),
	array('label'=>'Create OperationLog', 'url'=>array('create')),
	array('label'=>'Update OperationLog', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete OperationLog', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage OperationLog', 'url'=>array('admin')),
);
?>

<h1>View OperationLog #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'customer_id',
		'actions',
		'gets',
		'posts',
		'create_time',
	),
)); ?>
