<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
?>
<?php echo "<?php\n"; ?>
/**
 * <?php echo $this->pluralize($this->class2name($this->modelClass)); ?> (<?php echo $this->class2id($this->modelClass); ?>)
 * @var $this <?php echo $this->getControllerClass()."\n"; ?>
 * @var $model <?php echo $this->getModelClass()."\n"; ?>
 *
 * @author Putra Sudaryanto <putra.sudaryanto@gmail.com>
 * @copyright Copyright (c) 2015 Ommu Platform (ommu.co)
 * @link http://company.ommu.co
 * @contect (+62)856-299-4114
 *
 */

<?php
$label=$this->pluralize($this->class2name($this->modelClass));
echo "\t\$this->breadcrumbs=array(
	\t'$label'=>array('manage'),
	\t'Manage',
\t);\n";
?>
	$this->menu=array(
		array(
			'label' => Phrase::trans(307,0), 
			'url' => array('javascript:void(0);'),
			'itemOptions' => array('class' => 'search-button'),
			'linkOptions' => array('title' => Phrase::trans(307,0)),
		),
		array(
			'label' => Phrase::trans(308,0), 
			'url' => array('javascript:void(0);'),
			'itemOptions' => array('class' => 'grid-button'),
			'linkOptions' => array('title' => Phrase::trans(308,0)),
		),
	);

?>

<?php echo "<?php //begin.Search ?>\n";?>
<div class="search-form">
<?php echo "<?php \$this->renderPartial('_search',array(
	'model'=>\$model,
)); ?>\n"; ?>
</div>
<?php echo "<?php //end.Search ?>\n";?>

<?php echo "<?php //begin.Grid Option ?>\n";?>
<div class="grid-form">
<?php echo "<?php \$this->renderPartial('_option_form',array(
	'model'=>\$model,
)); ?>\n"; ?>
</div>
<?php echo "<?php //end.Grid Option ?>\n";?>

<div id="partial-<?php echo $this->class2id($this->modelClass); ?>">
	<?php 
	echo "<?php //begin.Messages ?>\n";
	echo "\t<div id=\"ajax-message\">\n";
	echo "\t<?php\n";
	echo "\tif(Yii::app()->user->hasFlash('error'))
		echo Utility::flashError(Yii::app()->user->getFlash('error'));
	if(Yii::app()->user->hasFlash('success'))
		echo Utility::flashSuccess(Yii::app()->user->getFlash('success'));
	?>\n";
	echo "\t</div>\n";
	echo "\t<?php //begin.Messages ?>\n";?>

	<div class="boxed">
		<?php echo "<?php //begin.Grid Item ?>\n";?>
		<?php echo "<?php"; ?> 
			$columnData   = $columns;
			array_push($columnData, array(
				'header' => Phrase::trans(151,0),
				'class'=>'CButtonColumn',
				'buttons' => array(
					'view' => array(
						'label' => 'view',
						'options' => array(							
							'class' => 'view',
							'off_address' => '',
							'target' => '_blank',
						),
						'url' => 'Yii::app()->controller->createUrl("site/view",array("id"=>$data->primaryKey))'),
					'update' => array(
						'label' => 'update',
						'options' => array(
							'class' => 'update'
						),
						'url' => 'Yii::app()->controller->createUrl("edit",array("id"=>$data->primaryKey))'),
					'delete' => array(
						'label' => 'delete',
						'options' => array(
							'class' => 'delete'
						),
						'url' => 'Yii::app()->controller->createUrl("delete",array("id"=>$data->primaryKey))')
				),
				'template' => '{view}|{update}|{delete}',
			));

			$this->widget('application.components.system.OGridView', array(
				'id'=>'<?php echo $this->class2id($this->modelClass); ?>-grid',
				'dataProvider'=>$model->search(),
				'filter'=>$model,
				'columns' => $columnData,
				'pager' => array('header' => ''),
			));
		?>
		<?php echo "<?php //end.Grid Item ?>\n";?>
	</div>
</div>