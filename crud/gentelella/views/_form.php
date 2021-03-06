<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

/* @var $model \yii\db\ActiveRecord */
$model = new $generator->modelClass();
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
	$safeAttributes = $model->attributes();
}

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$label = Inflector::camel2words($modelClass);

$tableSchema = $generator->tableSchema;
$primaryKey = $generator->getPrimaryKey($tableSchema);
$foreignKeys = $generator->getForeignKeys($tableSchema->foreignKeys);

$redactorCondition = 0;
$uploadCondition = 0;
$foreignCondition = 0;
$getFunctionCondition = 0;
$permissionCondition = 0;
$enumCondition = 0;
foreach ($tableSchema->columns as $column) {
	$commentArray = explode(',', $column->comment);
	if(in_array('redactor', $commentArray))
		$redactorCondition = 1;
	if(in_array('file', $commentArray))
		$uploadCondition = 1;
	if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys)) {
		$foreignCondition = 1;
		if(preg_match('/(smallint)/', $column->type))
			$smallintCondition = 1;
	}
	if($column->comment != '' && $column->comment[0] == '"')
		$getFunctionCondition = 1;
	if($column->name == 'permission')
		$permissionCondition = 1;
	if (is_array($column->enumValues) && count($column->enumValues) > 0)
		$enumCondition = 1;
}

$yaml = $generator->loadYaml('author.yaml');

echo "<?php\n";
?>
/**
 * <?php echo Inflector::pluralize($label); ?> (<?php echo Inflector::camel2id($modelClass); ?>)
 * @var $this app\components\View
 * @var $this <?php echo ltrim($generator->controllerClass)."\n"; ?>
 * @var $model <?php echo ltrim($generator->modelClass)."\n"; ?>
 * @var $form app\components\widgets\ActiveForm
 *
 * @author <?php echo $yaml['author'];?> <?php echo '<'.$yaml['email'].'>'."\n";?>
 * @contact <?php echo $yaml['contact']."\n";?>
 * @copyright Copyright (c) <?php echo date('Y'); ?> <?php echo $yaml['copyright']."\n";?>
 * @created date <?php echo date('j F Y, H:i')." WIB\n"; ?>
<?php if($generator->useModified) {?>
 * @modified date <?php echo date('j F Y, H:i')." WIB\n"; ?>
 * @modified by <?php echo $yaml['author'];?> <?php echo '<'.$yaml['email'].'>'."\n";?>
 * @contact <?php echo $yaml['contact']."\n";?>
<?php }?>
 * @link <?php echo $generator->link."\n";?>
 *
 */

use yii\helpers\Html;
<?php echo $uploadCondition ? "use ".ltrim('yii\helpers\Url', '\\').";\n" : '';?>
use app\components\widgets\ActiveForm;
<?php echo $redactorCondition ? "use ".ltrim('yii\redactor\widgets\Redactor', '\\').";\n" : '';
foreach ($tableSchema->columns as $column) {
	if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys) && preg_match('/(smallint)/', $column->type)) {
		$relationTableName = trim($foreignKeys[$column->name]);
		$relationClassName = $generator->generateClassName($relationTableName);
		echo "use ".$generator->replaceModel($relationClassName).";\n";
	}
}
if($redactorCondition) {?>

$redactorOptions = [
	'imageManagerJson' => ['/redactor/upload/image-json'],
	'imageUpload' => ['/redactor/upload/image'],
	'fileUpload' => ['/redactor/upload/file'],
	'plugins' => ['clips', 'fontcolor', 'imagemanager']
];
<?php }?>
?>

<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-form">

<?= "<?php "?>$form = ActiveForm::begin([
<?php if($uploadCondition) {?>
	'options' => [
		'class' => 'form-horizontal form-label-left',
		'enctype' => 'multipart/form-data',
	],
<?php } else {?>
	'options' => ['class' => 'form-horizontal form-label-left'],
<?php }?>
	'enableClientValidation' => <?php echo $uploadCondition ? 'false' : 'true';?>,
	'enableAjaxValidation' => false,
	//'enableClientScript' => true,
	'fieldConfig' => [
		'errorOptions' => [
			'encode' => false,
		],
	],
]); ?>

<?php echo "<?php "?>//echo $form->errorSummary($model);?>

<?php
foreach ($tableSchema->columns as $column) {
	$commentArray = explode(',', $column->comment);
	if($column->name[0] == '_')
		continue;
	if($column->autoIncrement || $column->isPrimaryKey || $column->phpType === 'boolean' || $column->comment == 'trigger' || ($column->dbType == 'tinyint(1)' && $column->name != 'permission') || in_array($column->name, array('creation_id','modified_id','updated_id','slug')))
		continue;

	if (in_array($column->name, $safeAttributes))
		echo "<?php " . $generator->generateActiveField($column->name) . "; ?>\n\n";
}
foreach ($tableSchema->columns as $column) {
	if($column->name[0] == '_')
		continue;
	if($column->phpType === 'boolean' || ($column->dbType == 'tinyint(1)' && !in_array($column->name, ['publish','headline','permission'])))
		echo "<?php " . $generator->generateActiveField($column->name) . "; ?>\n\n";
}
foreach ($tableSchema->columns as $column) {
	if($column->dbType == 'tinyint(1)' && $column->name == 'headline')
		echo "<?php " . $generator->generateActiveField($column->name) . "; ?>\n\n";
}
foreach ($tableSchema->columns as $column) {
	if($column->dbType == 'tinyint(1)' && $column->name == 'publish')
		echo "<?php " . $generator->generateActiveField($column->name) . "; ?>\n\n";
} ?>
<hr/>

<?php echo "<?php ";?>echo $form->field($model, 'submitButton')
	->submitButton(); ?>

<?= "<?php " ?>ActiveForm::end(); ?>

</div>