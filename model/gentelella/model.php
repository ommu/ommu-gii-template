<?php
/**
 * This is the template for generating the model class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\model\Generator */
/* @var $tableName string full table name */
/* @var $className string class name */
/* @var $queryClassName string query class name */
/* @var $tableSchema yii\db\TableSchema */
/* @var $properties array list of properties (property => [type, name. comment]) */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */

/**
 * Variable
 */
use ommu\gii\model\Generator;
use yii\helpers\Inflector;

$patternClass = [];
$patternClass[0] = '(Ommu)';
$patternClass[1] = '(Swt)';

/**
 * Condition
 */
$relationCondition = 0;
$i18n = 0;
$memberCondition = 0;
$userCondition = 0;
$memberUserCondition = 0;
$tagCondition = 0;
$tinyCondition = 0;
$publishCondition = 0;
$urlCondition = 0;
$licenseCondition = 0;
$permissionCondition = 0;
$dateCondition = 0;
$slugCondition = 0;
$uploadCondition = 0;
$serializeCondition = 0;
$jsonCondition = 0;
$useGetFunctionCondition = 0;
$primaryKeyTriggerCondition = 0;

$relationArray = [];
$inputPublicVariables = [];
$searchPublicVariables = [];
$arrayAttributeName = [];

if($tableType == Generator::TYPE_VIEW)
	$primaryKey = $viewPrimaryKey;
else
	$primaryKey = $generator->getPrimaryKey($tableSchema);

/**
 * foreignKeys Column
 */
$foreignKeys = $generator->getForeignKeys($tableSchema->foreignKeys);
$otherModels = [];
foreach ($foreignKeys as $key => $val) {
	$module = $tableSchema->columns[$key]->comment;
	if($module)
		$otherModels[] = $generator->getUseModel($module, $generator->generateClassName($val));
}

$yaml = $generator->loadYaml('author.yaml');

echo "<?php\n";
?>
/**
 * <?= $className."\n" ?>
 * 
 * @author <?php echo $yaml['author'];?> <?php echo '<'.$yaml['email'].'>'."\n";?>
 * @contact <?php echo $yaml['contact']."\n";?>
 * @copyright Copyright (c) <?php echo date('Y'); ?> <?php echo $yaml['copyright']."\n";?>
 * @created date <?php echo date('j F Y, H:i')." WIB\n"; ?>
<?php if($generator->useModified):?>
 * @modified date <?php echo date('j F Y, H:i')." WIB\n"; ?>
 * @modified by <?php echo $yaml['author'];?> <?php echo '<'.$yaml['email'].'>'."\n";?>
 * @contact <?php echo $yaml['contact']."\n";?>
<?php endif; ?>
 * @link <?php echo $generator->link."\n";?>
 *
 * This is the model class for table "<?= $generator->generateTableName($tableName) ?>".
 *
 * The followings are the available columns in table "<?= $generator->generateTableName($tableName) ?>":
<?php /* foreach ($properties as $property => $data): ?>
 * @property <?= "{$data['type']} \${$property}"  . ($data['comment'] ? ' ' . strtr($data['comment'], ["\n" => ' ']) : '') . "\n" ?>
<?php endforeach; */?>
<?php 
foreach ($tableSchema->columns as $column) {
	if($column->name[0] == '_')
		continue;

	$commentArray = explode(',', $column->comment);
	if(in_array('trigger[delete]', $commentArray) || in_array('user', $commentArray) || (!in_array($primaryKey, ['creation_id','modified_id','user_id','updated_id','tag_id','member_id']) && in_array($column->name, ['creation_id','modified_id','user_id','updated_id','tag_id','member_id']))) {
		$relationCondition = 1;
		if(in_array('trigger[delete]', $commentArray))
			$i18n = 1;
		if($column->name == 'member_id')
			$memberCondition = 1;
        if(in_array('user', $commentArray) || in_array($column->name, ['creation_id','modified_id','user_id','updated_id'])) {
            $userCondition = 1;
            if($memberCondition && $column->name == 'user_id') {
                $memberUserCondition = 1;
            }
        }
		if($column->name == 'tag_id')
			$tagCondition = 1;
	}

	if(in_array($column->dbType, ['tinyint(1)'])) {
		if($column->name != 'permission' && ($column->comment == '' || ($column->comment != '' && $column->comment[0] != '"')))
			$tinyCondition = 1;
		if(in_array($column->name, ['publish','headline']))
			$publishCondition = 1;
		if(!in_array($column->name, ['publish','headline']) && $column->comment != '' && $column->comment[0] != '"')
			$urlCondition = 1;
		if($column->name == 'permission')
			$permissionCondition = 1;
	}

	if(in_array($column->dbType, ['timestamp','datetime','date']))
		$dateCondition = 1;

	if($column->name == 'license') 
		$licenseCondition = 1;

	if($column->name == 'slug') 
		$slugCondition = 1;

	if($tableType != Generator::TYPE_VIEW && $column->type == 'text' && in_array('file', $commentArray))
		$uploadCondition = 1;

	if($tableType != Generator::TYPE_VIEW && $column->type == 'text' && in_array('serialize', $commentArray))
		$serializeCondition = 1;

	if($tableType != Generator::TYPE_VIEW && $column->type == 'text' && in_array('json', $commentArray))
		$jsonCondition = 1;?>
 * @property <?= "{$column->phpType} \${$column->name}\n" ?>
<?php }

if (!empty($relations) || $relationCondition) {?>
 *
 * The followings are the available model relations:
<?php foreach ($relations as $name => $relation) {
	$relationModel = preg_replace($patternClass, '', $relation[1]);
	$relationArray[] = $relationName = ($relation[2] ? lcfirst($generator->setRelation($name, true)) : $generator->setRelation($name));?>
 * @property <?= $relationModel . ($relation[2] ? '[]' : '') . ' $' . $relationName ."\n" ?>
<?php }
foreach ($tableSchema->columns as $column) {
	if($column->autoIncrement || ($column->isPrimaryKey && !array_key_exists($column->name, $foreignKeys) && !in_array($column->name, ['creation_id','modified_id','user_id','updated_id','tag_id','member_id'])))
		continue;
	if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys))
		continue;

	$commentArray = explode(',', $column->comment);
	if(in_array('user', $commentArray) || in_array($column->name, ['creation_id','modified_id','user_id','updated_id','tag_id','member_id'])) {
		$relationName = $generator->setRelation($column->name);
		$relationName = $generator->setRelationFixed($relationName, $tableSchema->columns);
		if(!in_array($relationName, $relationArray)) {
			$relationArray[] = $relationName;
			if($column->name == 'tag_id')
				echo " * @property CoreTags \${$relationName}\n";
			else if($column->name == 'member_id')
				echo " * @property Members \${$relationName}\n";
			else if(in_array($column->name, ['creation_id','modified_id','user_id','updated_id']))
				echo " * @property Users \${$relationName}\n";
			else if(in_array('user', $commentArray))
				echo " * @property Users \${$relationName}\n";
		}
	} else {
		if(in_array('trigger[delete]', $commentArray)) {
			$relationName = $generator->i18nRelation($column->name);
			echo " * @property SourceMessage \${$relationName}\n";
		}
	}
}

if($tableType != Generator::TYPE_VIEW) {
	$primaryKeyColumn = $tableSchema->columns[$primaryKey];
	if($primaryKeyColumn->type == 'smallint' || ($primaryKeyColumn->type == 'tinyint' && $primaryKeyColumn->dbType != 'tinyint(1)' && !$permissionCondition))
		$useGetFunctionCondition = 1;
	}
	if($primaryKeyColumn->comment == 'trigger')
		$primaryKeyTriggerCondition = 1;
}

$relationCondition = 0;
foreach ($relations as $name => $relation) {
	if($relation[2])
		$relationCondition = 1;
}
?>
 *
 */

namespace <?= $generator->ns ?>;

use Yii;
<?php 
echo $uploadCondition || $relationCondition ? "use ".ltrim('yii\helpers\Html', '\\').";\n" : '';
echo $publishCondition || $urlCondition || $uploadCondition ? "use ".ltrim('yii\helpers\Url', '\\').";\n" : '';
echo $i18n || $tagCondition || $slugCondition ? "use ".ltrim('yii\helpers\Inflector', '\\').";\n" : '';
echo $uploadCondition ? "use ".ltrim('yii\web\UploadedFile', '\\').";\n" : '';
echo $slugCondition ? "use ".ltrim('yii\behaviors\SluggableBehavior', '\\').";\n" : '';
echo $jsonCondition ? "use ".ltrim('yii\helpers\Json', '\\').";\n" : '';
echo $uploadCondition ? "use ".ltrim('thamtech\uuid\helpers\UuidHelper', '\\').";\n" : '';
echo $tagCondition ? "use ".ltrim('app\models\CoreTags', '\\').";\n" : '';
echo $i18n ? "use ".ltrim('app\models\SourceMessage', '\\').";\n" : '';
echo $userCondition ? "use ".ltrim('app\models\Users', '\\').";\n" : '';
echo $memberCondition ? "use ".ltrim('ommu\member\models\Members', '\\').";\n" : '';
if(!empty($otherModels)):
	foreach($otherModels as $val) {
		echo "use ".ltrim($val, '\\').";\n";
	}
endif;
?>

class <?= $className ?> extends <?= '\\' . ltrim($generator->baseClass, '\\') . "\n" ?>
{
<?php echo $tinyCondition || $licenseCondition || $primaryKeyTriggerCondition ? "\tuse \\".ltrim('\ommu\traits\UtilityTrait', '\\').";\n" : '';?>
<?php echo $uploadCondition ? "\tuse \\".ltrim('\ommu\traits\FileTrait', '\\').";\n" : '';?>
<?php echo $tinyCondition || $licenseCondition || $primaryKeyTriggerCondition || $uploadCondition ? "\n" : '';?>
	public $gridForbiddenColumn = [];
<?php 
foreach ($tableSchema->columns as $column) {
	$commentArray = explode(',', $column->comment);
	if($tableType != Generator::TYPE_VIEW && in_array('trigger[delete]', $commentArray)) {
		$inputPublicVariable = $column->name.'_i';
		if(!in_array($inputPublicVariable, $inputPublicVariables))
			$inputPublicVariables[$inputPublicVariable] = Inflector::camel2words(Inflector::id2camel($column->name));
	}
}
foreach ($tableSchema->columns as $column) {
	if($tableType != Generator::TYPE_VIEW && in_array($column->name, ['tag_id'])) {
		$relationName = $generator->setRelation($column->name);
		$inputPublicVariable = $relationName.ucwords('body');
		if(!in_array($inputPublicVariable, $inputPublicVariables))
			$inputPublicVariables[$inputPublicVariable] = ucwords(strtolower($relationName));
	}
}
foreach ($tableSchema->columns as $column) {
	$commentArray = explode(',', $column->comment);
	if($tableType != Generator::TYPE_VIEW && $column->type == 'text' && in_array('file', $commentArray)) {
		$inputPublicVariable = 'old_'.$column->name;
		if(!in_array($inputPublicVariable, $inputPublicVariables))
			$inputPublicVariables[$inputPublicVariable] = Inflector::camel2words('Old'.Inflector::id2camel($column->name));
	}
}

foreach ($tableSchema->columns as $column) {
	if($tableType != Generator::TYPE_VIEW && !empty($foreignKeys) && array_key_exists($column->name, $foreignKeys) && !in_array($column->name, ['tag_id'])) {
		$relationName = $generator->setRelation($column->name);
		$relationTable = trim($foreignKeys[$column->name]);
		$relationSchema = $generator->getTableSchemaWithTableName($relationTable);
		$relationAttribute = key($generator->getNameAttributes($relationSchema));
		if(in_array($relationTable, ['ommu_users', 'ommu_members']))
			$relationAttribute = 'displayname';
		$searchPublicVariable = $relationName.ucwords(Inflector::id2camel($relationAttribute, '_'));
		if(preg_match('/('.$relationName.')/', $relationAttribute))
			$searchPublicVariable = lcfirst(Inflector::id2camel($relationAttribute, '_'));

		if(!in_array($searchPublicVariable, $searchPublicVariables))
			$searchPublicVariables[$searchPublicVariable] = ucwords(strtolower($relationName));
	}
}
foreach ($tableSchema->columns as $column) {
	if($column->autoIncrement || ($column->isPrimaryKey && !array_key_exists($column->name, $foreignKeys) && !in_array($column->name, ['creation_id','modified_id','user_id','updated_id','tag_id','member_id'])) || ($memberUserCondition && $column->name == 'user_id'))
		continue;
	if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys))
		continue;

	$commentArray = explode(',', $column->comment);
	if($tableType != Generator::TYPE_VIEW && (in_array('user', $commentArray) || in_array($column->name, ['creation_id','modified_id','user_id','updated_id','member_id']))) {
		$relationName = $generator->setRelation($column->name);
		$searchPublicVariable = $relationName.ucwords('displayname');
		if(!in_array($searchPublicVariable, $searchPublicVariables))
			$searchPublicVariables[$searchPublicVariable] = ucwords(strtolower($relationName));
	}
}

foreach ($relations as $name => $relation) {
	if(!$relation[2])
		continue;
	$relationName = ($relation[2] ? lcfirst(Inflector::singularize($generator->setRelation($name, true))) : $generator->setRelation($name));
    if(!in_array($relationName, $searchPublicVariables))
        $searchPublicVariables[$relationName] = ucwords(strtolower($relationName));
}

if(!empty($inputPublicVariables) || !empty($searchPublicVariables))
	echo "\n";

if(!empty($inputPublicVariables)) {
	foreach ($inputPublicVariables as $key=>$val) {
		echo "\tpublic $$key;\n";
	}
}
if(!empty($searchPublicVariables)) {
	foreach ($searchPublicVariables as $key=>$val) {
		echo "\tpublic $$key;\n";
	}
}?>

	/**
	 * @return string the associated database table name
	 */
	public static function tableName()
	{
		return '<?= $generator->generateTableName($tableName) ?>';
	}
<?php if($tableType == Generator::TYPE_VIEW) {?>

	/**
	 * @return string the primarykey column
	 */
	public static function primaryKey()
	{
		return ['<?=$viewPrimaryKey?>'];
	}
<?php }

if ($generator->db !== 'db') {?>

	/**
	 * @return \yii\db\Connection the database connection used by this AR class.
	 */
	public static function getDb()
	{
		return Yii::$app->get('<?= $generator->db ?>');
	}
<?php }

if ($slugCondition) {
	$tableAttribute = $generator->getNameAttribute(null, '.'); ?>

	/**
	 * behaviors model class.
	 */
	public function behaviors() {
		return [
			[
				'class' => SluggableBehavior::className(),
				'attribute' => '<?php echo $tableAttribute;?>',
				'immutable' => true,
				'ensureUnique' => true,
			],
		];
	}
<?php }?>

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return [<?= empty($rules) ? '' : ("\n			" . implode(",\n			", preg_replace($patternClass, '', $rules)) . ",\n		") ?>];
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return [
<?php 
foreach ($labels as $name => $label) {
	if($name[0] == '_')
		continue;
	echo "\t\t\t'$name' => " . $generator->generateString($label) . ",\n";
}

if(!empty($inputPublicVariables)) {
	foreach ($inputPublicVariables as $key=>$val) {
        echo "\t\t\t'$key' => " . $generator->generateString($val) . ",\n";
	}
}

if(!empty($searchPublicVariables)) {
	foreach ($searchPublicVariables as $key=>$val) {
		echo "\t\t\t'$key' => " . $generator->generateString($val) . ",\n";
	}
} ?>
		];
	}
<?php 
$relationArray = [];
foreach ($relations as $name => $relation) {
	$relationArray[] = $relationName = ($relation[2] ? $generator->setRelation($name, true) : $generator->setRelation($name));
	$publishRltnCondition = 0;
	if(preg_match('/(%s.publish)/', $relation[0]))
		$publishRltnCondition = 1;?>

	/**
	 * @return \yii\db\ActiveQuery
	 */
<?php if($relation[2]) {?>
	public function get<?php echo ucfirst($relationName);?>($count=false<?php echo $publishRltnCondition ? ', $publish=1' : ''?>)
<?php } else {?>
	public function get<?php echo ucfirst($relationName);?>()
<?php }?>
	{
<?php if($relation[2]) {?>
        if ($count == false) {
            <?= preg_replace($patternClass, '', $relation[0]) . "\n" ?>
        }

<?php } else {?>
		<?= preg_replace($patternClass, '', $relation[0]) . "\n" ?>
<?php }?>
<?php if($relation[2]) {?>
		$model = <?php echo $relation[1];?>::find()
            ->alias('t')
            ->where(<?php echo $relation[3];?>);
<?php if($publishRltnCondition) {?>
        if ($publish == 0) {
            $model->unpublish();
        } else if ($publish == 1) {
            $model->published();
        } else if ($publish == 2) {
            $model->deleted();
        }
<?php }?>
		$<?php echo lcfirst($relationName);?> = $model->count();

		return $<?php echo lcfirst($relationName);?> ? $<?php echo lcfirst($relationName);?> : 0;
<?php }?>
	}
<?php }

if($i18n) {
	foreach ($tableSchema->columns as $column) {
		$commentArray = explode(',', $column->comment);
		if(in_array('trigger[delete]', $commentArray)) {
			$relationName = $generator->i18nRelation($column->name);
			if(!in_array($relationName, $relationArray)) {
				$relationArray[] = $relationName;?>

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function get<?php echo ucfirst($relationName);?>()
	{
		return $this->hasOne(SourceMessage::className(), ['id' => '<?php echo $column->name;?>']);
	}
<?php		}
		}
	}
}

foreach ($tableSchema->columns as $column) {
	if($column->autoIncrement || ($column->isPrimaryKey && !array_key_exists($column->name, $foreignKeys) && !in_array($column->name, ['creation_id','modified_id','user_id','updated_id','tag_id','member_id'])))
		continue;
	if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys))
		continue;

	$commentArray = explode(',', $column->comment);
	if(in_array('user', $commentArray) || in_array($column->name, ['creation_id','modified_id','user_id','updated_id','tag_id','member_id'])) {
		$relationName = $generator->setRelation($column->name);
		$relationName = $generator->setRelationFixed($relationName, $tableSchema->columns);
		if(!in_array($relationName, $relationArray)) {
			$relationArray[] = $relationName;
			$relationModelClass = 'Users';
			$relationAttribute = 'user_id';
			if($column->name == 'tag_id') {
				$relationModelClass = 'CoreTags';
				$relationAttribute = 'tag_id';
			}
			if($column->name == 'member_id') {
				$relationModelClass = 'Members';
				$relationAttribute = 'member_id';
			}?>

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function get<?php echo ucfirst($relationName);?>()
	{
		return $this->hasOne(<?php echo $relationModelClass;?>::className(), ['<?php echo $relationAttribute;?>' => '<?php echo $column->name;?>']);
	}
<?php 	}
	}
}

if($queryClassName): 
	$queryClassFullName = ($generator->ns === $generator->queryNs) ? $queryClassName : '\\' . $generator->queryNs . '\\' . $queryClassName;
	echo "\n";
?>
	/**
	 * {@inheritdoc}
	 * @return <?= $queryClassFullName ?> the active query used by this AR class.
	 */
	public static function find()
	{
		return new <?= $queryClassFullName ?>(get_called_class());
	}
<?php endif; ?>

	/**
	 * Set default columns to display
	 */
	public function init()
	{
        parent::init();

        if (!(Yii::$app instanceof \app\components\Application)) {
            return;
        }

        if (!$this->hasMethod('search')) {
            return;
        }

		$this->templateColumns['_no'] = [
			'header' => '#',
			'class' => 'app\components\grid\SerialColumn',
			'contentOptions' => ['class' => 'text-center'],
		];
<?php 
$publicAttributes = [];
$enumArray = [];
foreach ($tableSchema->columns as $column) {
	if($column->name[0] == '_')
		continue;
	if($column->autoIncrement || ($column->isPrimaryKey && !array_key_exists($column->name, $foreignKeys) && !in_array($column->name, ['creation_id','modified_id','user_id','updated_id','tag_id','member_id'])) || $column->phpType === 'boolean' || ($column->dbType == 'tinyint(1)' && $column->name != 'permission') || ($memberUserCondition && $column->name == 'user_id'))
		continue;

	$commentArray = explode(',', $column->comment);
	$foreignCondition = 0;
	if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys))
		$foreignCondition = 1;

	if($tableType != Generator::TYPE_VIEW && ($foreignCondition || in_array('user', $commentArray) || in_array($column->name, ['creation_id','modified_id','user_id','updated_id','tag_id','member_id']))) {
		$smallintCondition = 0;
		if(preg_match('/(smallint)/', $column->type))
			$smallintCondition = 1;
		$relationName = $generator->setRelation($column->name);
		$relationFixedName = $generator->setRelationFixed($relationName, $tableSchema->columns);
		$relationAttribute = $variableAttribute = 'displayname';
		$publicAttribute = $relationName.ucwords(Inflector::id2camel($variableAttribute, '_'));
		if(array_key_exists($column->name, $foreignKeys)) {
			$relationTable = trim($foreignKeys[$column->name]);
			$relationAttribute = $generator->getNameAttribute($relationTable);
			$relationSchema = $generator->getTableSchemaWithTableName($relationTable);
			$variableAttribute = key($generator->getNameAttributes($relationSchema));
			if(in_array($relationTable, ['ommu_users', 'ommu_members']))
				$relationAttribute = $variableAttribute = 'displayname';
			$publicAttribute = $relationName.ucwords(Inflector::id2camel($variableAttribute, '_'));
			if(preg_match('/('.$relationName.')/', $variableAttribute))
				$publicAttribute = lcfirst(Inflector::id2camel($variableAttribute, '_'));
		}
		if($column->name == 'tag_id') {
			$publicAttribute = $relationName.ucwords('body');
			$relationAttribute = 'body';
		}
		$publicProperty = $publicAttribute;
		if($smallintCondition)
			$publicAttribute = $column->name;

		if(!in_array($publicAttribute, $publicAttributes)) {
			$publicAttributes[] = $publicAttribute;?>
		$this->templateColumns['<?php echo $publicAttribute;?>'] = [
			'attribute' => '<?php echo $publicAttribute;?>',
			'value' => function($model, $key, $index, $column) {
<?php if ($memberUserCondition && $column->name == 'member_id') {?>
                $<?php echo $publicProperty;?> = isset($model-><?php echo $relationFixedName;?>) ? $model-><?php echo $relationFixedName;?>-><?php echo $relationAttribute;?> : '-';
                $userDisplayname = isset($model->user) ? $model->user->displayname : '-';
                if ($userDisplayname != '-' && $<?php echo $publicProperty;?> != $userDisplayname) {
                    return $<?php echo $publicProperty;?>.'<br/>'.$userDisplayname;
                }
                return $<?php echo $publicProperty;?>;
<?php } else {?>
				return isset($model-><?php echo $relationFixedName;?>) ? $model-><?php echo $relationFixedName;?>-><?php echo $relationAttribute;?> : '-';
<?php }?>
				// return $model-><?php echo $publicProperty;?>;
			},
<?php if($foreignCondition && $smallintCondition) {
	$relationClassName = $generator->generateClassName($relationTable);
	$relationFunctionName = Inflector::singularize($generator->setRelation($relationClassName, true));?>
			'filter' => <?php echo $relationClassName;?>::get<?php echo $relationFunctionName;?>(),
<?php }
if ($memberUserCondition && $column->name == 'member_id') {?>
            'format' => 'html',
<?php }?>
			'visible' => !Yii::$app->request->get('<?php echo $relationName;?>') ? true : false,
		];
<?php 	}
	} else if(in_array($column->dbType, ['timestamp','datetime','date'])) {
		$publicAttribute = $column->name;
		if(!in_array($publicAttribute, $publicAttributes)) {
			$publicAttributes[] = $publicAttribute;?>
		$this->templateColumns['<?php echo $publicAttribute;?>'] = [
			'attribute' => '<?php echo $publicAttribute;?>',
			'value' => function($model, $key, $index, $column) {
<?php if($column->dbType == 'date') {?>
				return Yii::$app->formatter->asDate($model-><?php echo $column->name;?>, 'medium');
<?php } else {?>
				return Yii::$app->formatter->asDatetime($model-><?php echo $column->name;?>, 'medium');
<?php }?>
			},
			'filter' => $this->filterDatepicker($this, '<?php echo $column->name;?>'),
		];
<?php 	}
	} else if($column->type == 'text' && in_array('file', $commentArray)) {
		$publicAttribute = $column->name;
		if(!in_array($publicAttribute, $publicAttributes)) {
			$publicAttributes[] = $publicAttribute;?>
		$this->templateColumns['<?php echo $publicAttribute;?>'] = [
			'attribute' => '<?php echo $publicAttribute;?>',
			'value' => function($model, $key, $index, $column) {
<?php if($generator->uploadPath['subfolder']) {?>
				$uploadPath = join('/', [self::getUploadPath(false), $model-><?php echo $primaryKey;?>]);
<?php } else {?>
				$uploadPath = self::getUploadPath(false);
<?php }?>
<?php if(in_array('pdf', $commentArray)) {?>
				return $model-><?php echo $publicAttribute;?> ? Html::a($model-><?php echo $publicAttribute;?>, Url::to(join('/', ['@webpublic', $uploadPath, $model-><?php echo $publicAttribute;?>])), ['title' => $model-><?php echo $publicAttribute;?>, 'target' => '_blank']) : '-';
			},
			'format' => 'raw',
<?php } else {?>
				return $model-><?php echo $publicAttribute;?> ? Html::img(Url::to(join('/', ['@webpublic', $uploadPath, $model-><?php echo $publicAttribute;?>])), ['alt' => $model-><?php echo $publicAttribute;?>]) : '-';
			},
			'format' => 'html',
<?php }?>
		];
<?php 	}
	} else if(is_array($column->enumValues)) {
		$publicAttribute = $column->name;
		if(!in_array($publicAttribute, $publicAttributes)) {
			$publicAttributes[] = $publicAttribute;
			if(!in_array($column->dbType, $enumArray)) {
				$enumArray[$column->name] = $column->dbType;
				$functionName = ucfirst($generator->setRelation($column->name));
			} else {
				$enumKey = array_flip($enumArray)[$column->dbType];
				$functionName = ucfirst($generator->setRelation($enumKey));
			}?>
		$this->templateColumns['<?php echo $publicAttribute;?>'] = [
			'attribute' => '<?php echo $publicAttribute;?>',
			'value' => function($model, $key, $index, $column) {
				return self::get<?php echo $functionName;?>($model-><?php echo $publicAttribute;?>);
			},
			'filter' => self::get<?php echo $functionName;?>(),
		];
<?php 	}
	} else {
		$publicAttribute = $column->name;
		$translateCondition = 0;
		if(in_array('trigger[delete]', $commentArray)) {
			$publicAttribute = $column->name.'_i';
			$publicAttributeRelation = $generator->i18nRelation($column->name);
			$translateCondition = 1;
		}
		if(!in_array($publicAttribute, $publicAttributes)) {
			$publicAttributes[] = $publicAttribute;?>
		$this->templateColumns['<?php echo $publicAttribute;?>'] = [
			'attribute' => '<?php echo $publicAttribute;?>',
			'value' => function($model, $key, $index, $column) {
<?php if($translateCondition) {?>
				return $model-><?php echo $publicAttribute;?>;
<?php } else {
	if($column->type == 'text' && in_array('serialize', $commentArray)) {?>
				return serialize($model-><?php echo $publicAttribute;?>);
<?php } else if($column->type == 'text' && in_array('json', $commentArray)) {?>
                if (is_array($model-><?php echo $publicAttribute;?>) && empty($model-><?php echo $publicAttribute;?>)) {
                    return '-';
                }
                return Json::encode($model-><?php echo $publicAttribute;?>);
<?php } else if($column->name == 'permission') {
		$functionName = ucfirst($generator->setRelation($column->name));?>
				return self::get<?php echo $functionName;?>($model-><?php echo $publicAttribute;?>);
<?php } else {?>
				return $model-><?php echo $publicAttribute;?>;
<?php }
}?>
			},
<?php if(in_array('redactor', $commentArray)) {?>
			'format' => 'html',
<?php }?>
		];
<?php 		}
	}
}

foreach ($relations as $name => $relation) {
	if(!$relation[2])
		continue;

	$publishRltnCondition = 0;
	if(preg_match('/(%s.publish)/', $relation[0]))
		$publishRltnCondition = 1;
	$relationName = ($relation[2] ? lcfirst($generator->setRelation($name, true)) : $generator->setRelation($name));
	$controller = Inflector::singularize($relationName) != $generator->getModuleName() ? Inflector::singularize($relationName) : 'admin'; ?>
		$this->templateColumns['<?php echo Inflector::singularize($relationName);?>'] = [
			'attribute' => '<?php echo Inflector::singularize($relationName);?>',
			'value' => function($model, $key, $index, $column) {
				$<?php echo lcfirst($relationName);?> = $model->get<?php echo ucfirst($relationName);?>(true);
				return Html::a($<?php echo lcfirst($relationName);?>, ['<?php echo $controller;?>/manage', '<?php echo $generator->setRelation($relation[4]);?>' => $model->primaryKey<?php echo $publishRltnCondition ? ', \'publish\' => 1' : '';?>], ['title' => Yii::t('app', '{count} <?php echo $relationName;?>', ['count' => $<?php echo lcfirst($relationName);?>]), 'data-pjax' => 0]);
			},
			'filter' => $this->filterYesNo(),
			'contentOptions' => ['class' => 'text-center'],
			'format' => 'raw',
		];
<?php }

foreach ($tableSchema->columns as $column) {
	$comment = $column->comment;
	if($column->name[0] == '_')
		continue;
	if($column->dbType == 'tinyint(1)' && (in_array($column->name, ['publish','headline','permission']) || ($comment != '' && $comment[7] != '[')))
		continue;
		
	if ($column->phpType === 'boolean' || $column->dbType == 'tinyint(1)') {?>
		$this->templateColumns['<?php echo $column->name;?>'] = [
			'attribute' => '<?php echo $column->name;?>',
			'value' => function($model, $key, $index, $column) {
				return $this->filterYesNo($model-><?php echo $column->name;?>);
			},
			'filter' => $this->filterYesNo(),
			'contentOptions' => ['class' => 'text-center'],
		];
<?php }
}

foreach ($tableSchema->columns as $column) {
	$comment = $column->comment;
	if($column->name[0] == '_')
		continue;
	if($column->phpType === 'boolean' || ($column->dbType == 'tinyint(1)' && in_array($column->name, ['publish','permission'])))
		continue;

	if($column->dbType == 'tinyint(1)' && ($column->name == 'headline' || ($comment != '' && $comment[7] != '['))) {
		if($column->name == 'headline' && $comment == '')
			$comment = 'Headline,Unheadline';?>
		$this->templateColumns['<?php echo $column->name;?>'] = [
			'attribute' => '<?php echo $column->name;?>',
			'value' => function($model, $key, $index, $column) {
<?php $rawCondition = 0;
if($comment != '' && $comment[0] == '"') {
	$functionName = ucfirst($generator->setRelation($column->name));?>
				return self::get<?php echo $functionName;?>($model-><?php echo $column->name;?>);
<?php } else {
	$rawCondition = 1;?>
				$url = Url::to(['<?php echo Inflector::camel2id($column->name);?>', 'id' => $model->primaryKey]);
				return $this->quickAction($url, $model-><?php echo $column->name;?>, '<?php echo $comment;?>'<?php echo $column->name == 'headline' ? ', true' : '';?>);
<?php }?>
			},
<?php if($comment != '' && $comment[0] == '"') {
	$functionName = ucfirst($generator->setRelation($column->name));?>
			'filter' => self::get<?php echo $functionName;?>(),
<?php } else {?>
			'filter' => $this->filterYesNo(),
<?php }?>
			'contentOptions' => ['class' => 'text-center'],
<?php if($rawCondition == 1) {?>
			'format' => 'raw',
<?php }?>
		];
<?php }
}

foreach ($tableSchema->columns as $column) {
	$comment = $column->comment;
	if($column->dbType == 'tinyint(1)' && $column->name == 'publish') {?>
		$this->templateColumns['<?php echo $column->name;?>'] = [
			'attribute' => '<?php echo $column->name;?>',
			'value' => function($model, $key, $index, $column) {
<?php if(!$primaryKeyTriggerCondition) {?>
				$url = Url::to(['<?php echo Inflector::camel2id($column->name);?>', 'id' => $model->primaryKey]);
				return $this->quickAction($url, $model-><?php echo $column->name;?><?php echo $comment != '' ? ", '$comment'" : '';?>);
<?php } else {?>
				return $this->filterYesNo($model->publish);
<?php }?>
			},
			'filter' => $this->filterYesNo(),
			'contentOptions' => ['class' => 'text-center'],
<?php echo !$primaryKeyTriggerCondition ? "\t\t\t'format' => 'raw',\n" : '';?>
			'visible' => !Yii::$app->request->get('trash') ? true : false,
		];
<?php }
} ?>
<?php /*
		if(count($this->defaultColumns) == 0) {
foreach ($tableSchema->columns as $column):
	if(!$column->isPrimaryKey) {
		if(in_array($column->dbType, ['timestamp','datetime','date'])) {?>
			$this->defaultColumns[] = [
				'attribute' => '<?php echo $column->name;?>',
				'filter'	=> \yii\jui\DatePicker::widget(['dateFormat' => Yii::$app->formatter->dateFormat,
					'attribute' => '<?php echo $column->name;?>',
					'model'  => $this,
				]),
				'format' => 'html',
			];
<?php } else { ?>
			$this->defaultColumns[] = [
				'attribute' => '<?php echo $column->name;?>',
				'class'  => 'yii\grid\DataColumn',
			];
<?php   }
	}
endforeach; 
		}
*/ ?>
	}

	/**
	 * User get information
	 */
	public static function getInfo($id, $column=null)
	{
        if ($column != null) {
            $model = self::find();
            if (is_array($column)) {
                $model->select($column);
            } else {
                $model->select([$column]);
            }
            $model = $model->where(['<?php echo $primaryKey;?>' => $id])->one();
            return is_array($column) ? $model : $model->$column;

        } else {
            $model = self::findOne($id);
            return $model;
        }
	}
<?php
if($tableType != Generator::TYPE_VIEW && ($generator->useGetFunction || $useGetFunctionCondition)) {
	$functionName = Inflector::singularize($generator->setRelation($className, true));
	$attributeName = key($generator->getNameAttributes($tableSchema));?>

	/**
	 * function get<?php echo $functionName."\n"; ?>
	 */
	public static function get<?php echo $functionName; ?>(<?php echo $publishCondition ? '$publish=null, $array=true' : '$array=true';?>) 
	{
		$model = self::find()->alias('t')
			->select(['t.<?php echo $primaryKey;?>', 't.<?php echo $attributeName;?>']);
<?php 
$i18nRelation = $i18n && preg_match('/(name|title)/', $attributeName) ? 'title' : '';
if($i18nRelation)
	echo "\t\t\$model->leftJoin(sprintf('%s $i18nRelation', SourceMessage::tableName()), 't.$attributeName=$i18nRelation.id');\n";
	
if($publishCondition) {?>
        if ($publish != null) {
            $model->andWhere(['t.publish' => $publish]);
        }

<?php }?>
		$model = $model->orderBy('<?php echo $i18nRelation ? $i18nRelation.'.message' : 't.'.$attributeName;?> ASC')->all();

        if ($array == true) {
            return \yii\helpers\ArrayHelper::map($model, '<?php echo $primaryKey;?>', '<?php echo $i18n && preg_match('/(name|title)/', $attributeName) ? $attributeName.'_i' : $attributeName;?>');
        }

		return $model;
	}
<?php }

foreach ($tableSchema->columns as $column) {
	if(!($column->dbType == 'tinyint(1)' && $column->name == 'permission'))
		continue;

	$functionName = ucfirst($generator->setRelation($column->name));?>

	/**
	 * function get<?php echo $functionName."\n"; ?>
	 */
	public static function get<?php echo $functionName; ?>($value=null)
	{
		$moduleName = "module name";
		$module = strtolower(Yii::$app->controller->module->id);
        if (($module = Yii::$app->moduleManager->getModule($module)) != null) {
            $moduleName = strtolower($module->getName());
        }

		$items = array(
			1 => Yii::t('app', 'Yes, the public can view {module} unless they are made private.', ['module' => $moduleName]),
			0 => Yii::t('app', 'No, the public cannot view {module}.', ['module' => $moduleName]),
		);

        if ($value !== null) {
            return $items[$value];
        } else {
            return $items;
        }
	}
<?php }

$enumArray = [];
foreach ($tableSchema->columns as $column) {
	if((is_array($column->enumValues) && !in_array($column->dbType, $enumArray)) || ($column->dbType == 'tinyint(1)' && $column->comment != '' && $column->comment[0] == '"')) {
		$functionName = ucfirst($generator->setRelation($column->name));
		if(is_array($column->enumValues))
			$enumArray[$column->name] = $column->dbType;?>

	/**
	 * function get<?php echo $functionName."\n"; ?>
	 */
	public static function get<?php echo $functionName; ?>($value=null)
	{
		$items = array(
<?php $comment = trim($column->comment, '"');
if($comment != '')
	$dropDownOptions = $generator->comment2Array($comment);

if(is_array($column->enumValues)) {
	foreach($column->enumValues as $enumValue) {?>
			'<?php echo $enumValue;?>' => <?php echo $generator->generateString(ucfirst(strtolower($comment != '' ? $dropDownOptions[$enumValue] : $enumValue)));?>,
<?php }
} else {
	foreach($dropDownOptions as $key=>$val) {?>
			'<?php echo $key;?>' => <?php echo $generator->generateString(ucfirst(strtolower($val)));?>,
<?php }
}?>
		);

        if ($value !== null) {
            return $items[$value];
        } else {
            return $items;
        }
	}
<?php }
}

if($uploadCondition) {
	$directoryPath = $generator->uploadPath['directory'];
	$returnAlias = join('/', ['@public', $directoryPath]);?>

	/**
	 * @param returnAlias set true jika ingin kembaliannya path alias atau false jika ingin string
	 * relative path. default true.
	 */
	public static function getUploadPath($returnAlias=true) 
	{
		return ($returnAlias ? Yii::getAlias('<?php echo $returnAlias;?>') : '<?php echo $directoryPath;?>');
	}
<?php }

$afEvents = 0;
if($tagCondition || $uploadCondition || $serializeCondition || $jsonCondition || $i18n || $userCondition || !empty($relations) || $relationCondition)
	$afEvents = 1;
if($tableType != Generator::TYPE_VIEW && $afEvents) {?>

	/**
	 * after find attributes
	 */
	public function afterFind()
	{
		parent::afterFind();

<?php foreach ($tableSchema->columns as $column) {
	$commentArray = explode(',', $column->comment);
	
	if(in_array($column->name, ['tag_id'])) {
		$relationName = $generator->setRelation($column->name);
		$publicAttribute = $relationName.ucwords('body');
		echo "\t\t\$this->$publicAttribute = isset(\$this->{$relationName}) ? \$this->{$relationName}->body : '';\n";

	} else if($column->type == 'text' && in_array('file', $commentArray)) {
		$inputPublicVariable = 'old_'.$column->name;
		echo "\t\t\$this->$inputPublicVariable = \$this->$column->name;\n";

	} else if($column->type == 'text' && in_array('serialize', $commentArray)) {
		echo "\t\t\$this->$column->name = unserialize(\$this->$column->name);\n";

	} else if($column->type == 'text' && in_array('json', $commentArray)) { ?>
        if ($this-><?php echo $column->name;?> == '') {
            $this-><?php echo $column->name;?> = [];
        } else {
            $this-><?php echo $column->name;?> = Json::decode($this-><?php echo $column->name;?>);
        }
<?php } else {
		if(in_array('trigger[delete]', $commentArray)) {
			$publicAttribute = $column->name.'_i';
			$publicAttributeRelation = $generator->i18nRelation($column->name);
			echo "\t\t\$this->$publicAttribute = isset(\$this->{$publicAttributeRelation}) ? \$this->{$publicAttributeRelation}->message : '';\n";
		}
	}
}

foreach ($tableSchema->columns as $column) {
	$commentArray = explode(',', $column->comment);
	$foreignCondition = 0;
	if(!empty($foreignKeys) && array_key_exists($column->name, $foreignKeys))
		$foreignCondition = 1;
	
	if($foreignCondition || in_array('user', $commentArray) || in_array($column->name, ['creation_id','modified_id','user_id','updated_id','member_id'])) {
        if ($memberUserCondition && $column->name == 'user_id')
            continue;

		$relationName = $generator->setRelation($column->name);
		$relationFixedName = $generator->setRelationFixed($relationName, $tableSchema->columns);
		$relationAttribute = 'displayname';
		$publicAttribute = $relationName.ucwords(Inflector::id2camel($relationAttribute, '_'));
		if(array_key_exists($column->name, $foreignKeys)) {
			$relationTable = trim($foreignKeys[$column->name]);
			$relationSchema = $generator->getTableSchemaWithTableName($relationTable);
			$relationAttribute = key($generator->getNameAttributes($relationSchema));
			if(in_array($relationTable, ['ommu_users', 'ommu_members']))
				$relationAttribute = 'displayname';
			$publicAttribute = $relationName.ucwords(Inflector::id2camel($relationAttribute, '_'));
			if(preg_match('/('.$relationName.')/', $relationAttribute))
				$publicAttribute = lcfirst(Inflector::id2camel($relationAttribute, '_'));
			$relationAttribute = in_array($relationTable, ['ommu_users', 'ommu_members']) ? 'displayname' : $generator->getNameAttribute($relationTable);
		}
		echo "\t\t// \$this->$publicAttribute = isset(\$this->{$relationFixedName}) ? \$this->{$relationFixedName}->{$relationAttribute} : '-';\n";
	}
}

foreach ($relations as $name => $relation) {
	if(!$relation[2])
		continue;
    $relationName = ($relation[2] ? $generator->setRelation($name, true) : $generator->setRelation($name));
    echo "\t\t\$this->".Inflector::singularize(lcfirst($relationName))." = \$this->get".ucfirst($relationName)."(true) ? 1 : 0;\n";
}?>
	}
<?php }

$bvEvents = 0;
$beforeValidate = 0;
$creationCondition = 0;
$userValidateCondition = 0;
if($uploadCondition)
	$bvEvents = 1;
foreach($tableSchema->columns as $column)
{
	$nameArray = explode('_', $column->name);
	if(in_array($column->name, ['creation_id','modified_id','user_id','updated_id']) && $column->comment != 'trigger')
		$bvEvents = 1;
	if(in_array('ip', $nameArray))
		$bvEvents = 1;
}
if($tableType != Generator::TYPE_VIEW && !$primaryKeyTriggerCondition && ($generator->generateEvents || $bvEvents)) {?>

	/**
	 * before validate attributes
	 */
	public function beforeValidate()
	{
        if (parent::beforeValidate()) {
<?php if($uploadCondition) {
	$beforeValidate = 1;
	foreach($tableSchema->columns as $column) {
		$commentArray = explode(',', $column->comment);
		if($column->type == 'text' && in_array('file', $commentArray)) {
			$fileType = lcfirst(Inflector::singularize(Inflector::id2camel($column->name, '_')).'FileType');?>
            // $this-><?php echo $column->name;?> = UploadedFile::getInstance($this, '<?php echo $column->name;?>');
            if ($this-><?php echo $column->name;?> instanceof UploadedFile && !$this-><?php echo $column->name;?>->getHasError()) {
                $<?php echo $fileType;?> = ['jpg', 'jpeg', 'png', 'bmp', 'gif'];
                if (!in_array(strtolower($this-><?php echo $column->name;?>->getExtension()), $<?php echo $fileType;?>)) {
                    $this->addError('<?php echo $column->name;?>', Yii::t('app', 'The file {name} cannot be uploaded. Only files with these extensions are allowed: {extensions}', [
                        'name' => $this-><?php echo $column->name;?>->name,
                        'extensions' => $this->formatFileType($<?php echo $fileType;?>, false),
                    ]));
                }
            } /* else {
                if ($this->isNewRecord || (!$this->isNewRecord && $this->old_<?php echo $column->name;?> == '')) {
                    $this->addError('<?php echo $column->name;?>', Yii::t('app', '{attribute} cannot be blank.', ['attribute' => $this->getAttributeLabel('<?php echo $column->name;?>')]));
                }
            } */

<?php 	}
	}
}

foreach($tableSchema->columns as $column) {
	if(in_array($column->name, ['creation_id','modified_id','updated_id','user_id']) && $column->comment != 'trigger') {
		$userValidateCondition = 1;
		$beforeValidate = 1;
		if(in_array($column->name, array('creation_id','user_id'))) {
			$creationCondition = 1;?>
            if ($this->isNewRecord) {
                if ($this-><?php echo $column->name;?> == null) {
                    $this-><?php echo $column->name;?> = !Yii::$app->user->isGuest ? Yii::$app->user->id : null;
                }
<?php } else {
    if($creationCondition) {?>
            } else {
<?php } else {?>
            if (!$this->isNewRecord) {
<?php }?>
                if ($this-><?php echo $column->name;?> == null) {
                    $this-><?php echo $column->name;?> = !Yii::$app->user->isGuest ? Yii::$app->user->id : null;
                }
<?php   }
	}
}
if($userValidateCondition) {?>
            }
<?php }

foreach($tableSchema->columns as $column) {
	$nameArray = explode('_', $column->name);
	if(in_array('ip', $nameArray)) {
		$beforeValidate = 1;?>
            $this-><?php echo $column->name;?> = $_SERVER['REMOTE_ADDR'];
<?php }
}
echo !$beforeValidate ? "\t\t\t// Create action\n" : '';?>
        }
        return true;
	}
<?php }

$avEvents = 0;
$afterValidate = 0;
if($tableType != Generator::TYPE_VIEW && !$primaryKeyTriggerCondition && ($generator->generateEvents || $avEvents)): ?>

	/**
	 * after validate attributes
	 */
	public function afterValidate()
	{
        parent::afterValidate();

        // Create action
        
        return true;
	}
<?php 
endif;

$bsEvents = 0;
$beforeSave = 0;
if($tagCondition || $uploadCondition || $serializeCondition || $jsonCondition || $i18n)
	$bsEvents = 1;
foreach($tableSchema->columns as $column) {
	if((in_array($column->type, ['date','datetime']) && $column->comment != 'trigger'))
		$bsEvents = 1;
}
if($tableType != Generator::TYPE_VIEW && !$primaryKeyTriggerCondition && ($generator->generateEvents || $bsEvents)): ?>

	/**
	 * before save attributes
	 */
	public function beforeSave($insert)
	{
<?php if($i18n) {
$beforeSave = 1;?>
        $module = strtolower(Yii::$app->controller->module->id);
        $controller = strtolower(Yii::$app->controller->id);
        $action = strtolower(Yii::$app->controller->action->id);

        $location = Inflector::slug($module.' '.$controller);

<?php }?>
        if (parent::beforeSave($insert)) {
<?php if($uploadCondition) {
$beforeSave = 1;?>
            if (!$insert) {
<?php if($generator->uploadPath['subfolder']) {?>
                $uploadPath = join('/', [self::getUploadPath(), $this-><?php echo $primaryKey;?>]);
<?php } else {?>
                $uploadPath = self::getUploadPath();
<?php }?>
                $verwijderenPath = join('/', [self::getUploadPath(), 'verwijderen']);
                $this->createUploadDirectory(self::getUploadPath()<?php echo $generator->uploadPath['subfolder'] ? ', $this->'.$primaryKey : '';?>);

<?php foreach($tableSchema->columns as $column) {
	$commentArray = explode(',', $column->comment);
	if($column->type == 'text' && in_array('file', $commentArray)) {?>
                // $this-><?php echo $column->name;?> = UploadedFile::getInstance($this, '<?php echo $column->name;?>');
                if ($this-><?php echo $column->name;?> instanceof UploadedFile && !$this-><?php echo $column->name;?>->getHasError()) {
<?php if($generator->uploadPath['subfolder']) {?>
                    $fileName = join('-', [time(), UuidHelper::uuid()]).'.'.strtolower($this-><?php echo $column->name;?>->getExtension()); 
<?php } else {?>
                    $fileName = join('-', [time(), UuidHelper::uuid(), $this-><?php echo $primaryKey;?>]).'.'.strtolower($this-><?php echo $column->name;?>->getExtension()); 
<?php }?>
                    if ($this-><?php echo $column->name;?>->saveAs(join('/', [$uploadPath, $fileName]))) {
                        if ($this->old_<?php echo $column->name;?> != '' && file_exists(join('/', [$uploadPath, $this->old_<?php echo $column->name;?>]))) {
                            rename(join('/', [$uploadPath, $this->old_<?php echo $column->name;?>]), join('/', [$verwijderenPath, $this-><?php echo $primaryKey;?>.'-'.time().'_change_'.$this->old_<?php echo $column->name;?>]));
                        }
                        $this-><?php echo $column->name;?> = $fileName;
                    }
                } else {
                    if ($this-><?php echo $column->name;?> == '') {
                        $this-><?php echo $column->name;?> = $this->old_<?php echo $column->name;?>;
                    }
                }

<?php }
}?>
            }
<?php }

foreach($tableSchema->columns as $column) {
	$commentArray = explode(',', $column->comment);
	if(in_array('trigger[delete]', $commentArray)) {
		$beforeSave = 1;
		$publicAttribute = $column->name.'_i';
		$publicAttributeLocation = preg_match('/(name|title)/', $column->name) ? '_title' : (preg_match('/(desc|description)/', $column->name) ? ($column->name != 'description' ? '_description' : '_'.$column->name) : '_'.$column->name);?>
            if ($insert || (!$insert && !$this-><?php echo $column->name;?>)) {
                $<?php echo $column->name;?> = new SourceMessage();
                $<?php echo $column->name;?>->location = $location.'<?php echo $publicAttributeLocation;?>';
                $<?php echo $column->name;?>->message = $this-><?php echo $publicAttribute;?>;
                if ($<?php echo $column->name;?>->save()) {
                    $this-><?php echo $column->name;?> = $<?php echo $column->name;?>->id;
                }
<?php if($slugCondition && $i18n && preg_match('/(name|title)/', $column->name)) {?>

                $this->slug = Inflector::slug($this-><?php echo $publicAttribute;?>);
<?php }?>

            } else {
                $<?php echo $column->name;?> = SourceMessage::findOne($this-><?php echo $column->name;?>);
                $<?php echo $column->name;?>->message = $this-><?php echo $publicAttribute;?>;
                $<?php echo $column->name;?>->save();
            }

<?php }
}

foreach($tableSchema->columns as $column) {
	$commentArray = explode(',', $column->comment);
	if(in_array($column->type, ['date','datetime']) && $column->comment != 'trigger') {
		$beforeSave = 1; ?>
            $this-><?php echo $column->name;?> = Yii::$app->formatter->asDate($this-><?php echo $column->name;?>, 'php:Y-m-d');
<?php } else if($column->type == 'text' && in_array('serialize', $commentArray)) {
		$beforeSave = 1;
		echo "\t\t\t\$this->$column->name = serialize(\$this->$column->name);\n";

	} else if($column->type == 'text' && in_array('json', $commentArray)) {
		$beforeSave = 1;
		echo "\t\t\t\$this->$column->name = Json::encode(\$this->$column->name);\n";

	} else if($column->name == 'tag_id') {
		$beforeSave = 1;
		$relationName =  $generator->setRelation($column->name);
		$publicAttribute = $relationName.ucwords('body');?>
            if ($insert) {
                $<?php echo $publicAttribute;?> = Inflector::slug($this-><?php echo $publicAttribute;?>);
                if ($this-><?php echo $column->name;?> == 0) {
                    $<?php echo $relationName;?> = CoreTags::find()
                        ->select(['<?php echo $column->name;?>'])
                        ->andWhere(['body' => $<?php echo $publicAttribute;?>])
                        ->one();
                        
                    if ($<?php echo $relationName;?> != null) {
                        $this-><?php echo $column->name;?> = $<?php echo $relationName;?>-><?php echo $column->name;?>;
                    } else {
                        $data = new CoreTags();
                        $data->body = $this-><?php echo $publicAttribute;?>;
                        if($data->save())
                            $this-><?php echo $column->name;?> = $data-><?php echo $column->name;?>;
                    }
                }
            }
<?php }
}
echo !$beforeSave ? "\t\t\t// Create action\n" : '';?>
        }
        return true;
	}
<?php 
endif;

$asEvents = 0;
$afterSave = 0;
if($uploadCondition)
	$asEvents = 1;
if($tableType != Generator::TYPE_VIEW && !$primaryKeyTriggerCondition && ($generator->generateEvents || $asEvents)) {?>

	/**
	 * After save attributes
	 */
	public function afterSave($insert, $changedAttributes)
	{
        parent::afterSave($insert, $changedAttributes);

<?php if($uploadCondition) {
$afterSave = 1;
if($generator->uploadPath['subfolder']) {?>
        $uploadPath = join('/', [self::getUploadPath(), $this-><?php echo $primaryKey;?>]);
<?php } else {?>
        $uploadPath = self::getUploadPath();
<?php }?>
        $verwijderenPath = join('/', [self::getUploadPath(), 'verwijderen']);
        $this->createUploadDirectory(self::getUploadPath()<?php echo $generator->uploadPath['subfolder'] ? ', $this->'.$primaryKey : '';?>);

        if ($insert) {
<?php foreach($tableSchema->columns as $column) {
	$commentArray = explode(',', $column->comment);
	if($column->type == 'text'  && in_array('file', $commentArray)) {?>
            // $this-><?php echo $column->name;?> = UploadedFile::getInstance($this, '<?php echo $column->name;?>');
            if ($this-><?php echo $column->name;?> instanceof UploadedFile && !$this-><?php echo $column->name;?>->getHasError()) {
<?php if($generator->uploadPath['subfolder']) {?>
                $fileName = join('-', [time(), UuidHelper::uuid()]).'.'.strtolower($this-><?php echo $column->name;?>->getExtension()); 
<?php } else {?>
                $fileName = join('-', [time(), UuidHelper::uuid(), $this-><?php echo $primaryKey;?>]).'.'.strtolower($this-><?php echo $column->name;?>->getExtension()); 
<?php }?>
                if ($this-><?php echo $column->name;?>->saveAs(join('/', [$uploadPath, $fileName]))) {
                    self::updateAll(['<?php echo $column->name;?>' => $fileName], ['<?php echo $primaryKey;?>' => $this-><?php echo $primaryKey;?>]);
                }
            }

<?php }
}?>
        }
<?php }
echo !$afterSave ? "\t\t// Create action\n" : '';?>
	}
<?php }

$bdEvents = 0;
$beforeDelete = 0;
if($tableType != Generator::TYPE_VIEW && !$primaryKeyTriggerCondition && ($generator->generateEvents || $bdEvents)): ?>

	/**
	 * Before delete attributes
	 */
	public function beforeDelete()
	{
        if (parent::beforeDelete()) {
            // Create action
        }
        return true;
	}
<?php 
endif;

$adEvents = 0;
$afterDelete = 0;
if($uploadCondition)
	$adEvents = 1;
if($tableType != Generator::TYPE_VIEW && !$primaryKeyTriggerCondition && ($generator->generateEvents || $adEvents)) {?>

	/**
	 * After delete attributes
	 */
	public function afterDelete()
	{
        parent::afterDelete();

<?php if($uploadCondition) {
	$afterDelete = 1;
if($generator->uploadPath['subfolder']) {?>
		$uploadPath = join('/', [self::getUploadPath(), $this-><?php echo $primaryKey;?>]);
<?php } else {?>
		$uploadPath = self::getUploadPath();
<?php }?>
		$verwijderenPath = join('/', [self::getUploadPath(), 'verwijderen']);

<?php foreach($tableSchema->columns as $column) {
	$commentArray = explode(',', $column->comment);
	if($column->type == 'text' && in_array('file', $commentArray)) {?>
        if ($this-><?php echo $column->name;?> != '' && file_exists(join('/', [$uploadPath, $this-><?php echo $column->name;?>]))) {
            rename(join('/', [$uploadPath, $this-><?php echo $column->name;?>]), join('/', [$verwijderenPath, $this-><?php echo $primaryKey;?>.'-'.time().'_deleted_'.$this-><?php echo $column->name;?>]));
        }

<?php 	}
	}
}
echo !$afterDelete ? "\t\t// Create action\n" : '';?>
	}
<?php }?>
}
