<?php
/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator yii\gii\generators\form\Generator */

echo $form->field($generator, 'viewName', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('viewName'));

echo $form->field($generator, 'modelClass', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('modelClass'));

echo $form->field($generator, 'scenarioName', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('scenarioName'));

echo $form->field($generator, 'viewPath', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('viewPath'));

echo $form->field($generator, 'enableI18N', ['horizontalCssClasses' => ['wrapper'=>'col-sm-9 col-xs-12 col-12']])
	->checkbox()
	->label($generator->getAttributeLabel('enableI18N'));

echo $form->field($generator, 'messageCategory', ['template' => '{label}{beginWrapper}{input}{error}{endWrapper}{hint}', 'horizontalCssClasses' => ['wrapper'=>'col-sm-9 col-xs-12 col-12']])
	->label($generator->getAttributeLabel('messageCategory'));
