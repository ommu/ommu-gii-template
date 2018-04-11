<?php
/**
 * This is the template for generating the model class of a specified table.
 * - $this: the ModelCode object
 * - $tableName: the table name for this class (prefix is already removed if necessary)
 * - $modelClass: the model class name
 * - $columns: list of table columns (name=>CDbColumnSchema)
 * - $labels: list of attribute labels (name=>label)
 * - $rules: list of validation rules
 * - $relations: list of relations (name=>relation declaration)
 */
 
/* 
* set name relation with underscore
*/
function setRelationName($names, $column=false) {
	$patterns = array();
	$patterns[0] = '(_ommu)';
	$patterns[1] = '(_core)';
	
	if($column == false) {
		$char=range("A","Z");
		foreach($char as $val) {
			if(strpos($names, $val) !== false) {
				$names = str_replace($val, '_'.strtolower($val), $names);
			}
		}
	} else
		$names = rtrim($names, 'id');

	$return = trim(preg_replace($patterns, '', $names), '_');
	$return = array_map('strtolower', explode('_', $return));
	//print_r($return);

	if(count($return) != 1)
		return end($return);
	else {
		if(is_array($return))
			return implode('', $return);
		else
			return $return;
	}
}

function guessNameColumn($columns)
{
	//echo '<pre>';
	//print_r($columns);
	$primaryKey = array();
	foreach ($columns as $key => $column) {
		if($column->isPrimaryKey || $column->autoIncrement)
			$primaryKey[] = $key;
		if(preg_match('/(name|title)/', $key))
			return $key;
	}
	$pk = $primaryKey;

	if(!empty($primaryKey))
		return $pk[0];
	else
		return 'id';
}

$publishCondition = 0;
$slugCondition = 0;
$uploadCondition = 0;
$i18n = 0;
$primaryKeyColumn = key($columns);
//echo '<pre>';
//print_r($columns);
foreach($columns as $name=>$column):
	if($column->dbType == 'tinyint(1)' && in_array($column->name, array('publish','headline')))
		$publishCondition = 1;
	if($column->name == 'slug')
		$slugCondition = 1;
	if($column->comment == 'file')
		$uploadCondition = 1;
endforeach;

?>
<?php echo "<?php\n"; ?>
/**
 * <?php echo $modelClass."\n"; ?>
 *
 * @author Putra Sudaryanto <putra@sudaryanto.id>
 * @contact (+62)856-299-4114
 * @copyright Copyright (c) <?php echo date('Y'); ?> Ommu Platform (opensource.ommu.co)
 * @created date <?php echo date('j F Y, H:i')." WIB\n"; ?>
<?php if($this->modifiedStatus):?>
 * @modified date <?php echo date('j F Y, H:i')." WIB\n"; ?>
<?php endif; ?>
 * @link <?php echo $this->linkSource."\n";?>
 *
 * This is the model class for table "<?php echo $tableName; ?>".
 *
 * The followings are the available columns in table '<?php echo $tableName; ?>':
<?php foreach($columns as $column): ?>
 * @property <?php echo $column->type.' $'.$column->name."\n"; ?>
<?php endforeach; ?>
<?php if(!empty($relations)): ?>
 *
 * The followings are the available model relations:
<?php 
//echo '<pre>';
//print_r($relations);
$availableRelations = array();
foreach($relations as $name=>$relation): ?>
 * @property <?php
	if (preg_match("~^array\(self::([^,]+), '([^']+)', '([^']+)'\)$~", $relation, $matches))
	{
		$relationType = $matches[1];
		if(preg_match('/Core/', $matches[2]))
			$relationModel = preg_replace('(Core)', '', $matches[2]);
		else
			$relationModel = preg_replace('(Ommu)', '', $matches[2]);
		$relationName = setRelationName($name);
		if($relationName == 'cat')
			$relationName = 'category';
			
		$availableRelations[] = $relationName;

		switch($relationType){
			case 'HAS_ONE':
				echo $relationModel.' $'.$relationName."\n";
			break;
			case 'BELONGS_TO':
				echo $relationModel.' $'.$relationName."\n";
			break;
			case 'HAS_MANY':
				echo $relationModel.'[] $'.$relationName."\n";
			break;
			case 'MANY_MANY':
				echo $relationModel.'[] $'.$relationName."\n";
			break;
			default:
				echo 'mixed $'.$name."\n";
		}
	}
endforeach;
//print_r($availableRelations);
foreach($labels as $name=>$label):
	if(in_array($name, array('creation_id','modified_id','user_id','updated_id','member_id'))) {
		$relationArray = explode('_', $name);
		$relationName = $relationArray[0];
		if(!in_array($relationName, $availableRelations)) {
			if($name == 'member_id')
				echo " * @property Members \${$relationName}\n";
			else
				echo " * @property Users \${$relationName}\n";
			
			$availableRelations[] = $relationName;
		}
	} else if($name == 'tag_id') {
		$relationArray = explode('_', $name);
		$relationName = $relationArray[0];
		if(!in_array($relationName, $availableRelations)) {
			echo " * @property OmmuTags \${$relationName}\n";
			
			$availableRelations[] = $relationName;
		}
	}
endforeach;
endif; ?>
 */

class <?php echo $modelClass; ?> extends <?php echo $this->baseClass."\n"; ?>
{
	public $gridForbiddenColumn = array();
<?php 
$publicVariable = array();
//echo '<pre>';
//print_r($columns);
foreach($columns as $name=>$column):
	$commentArray = explode(',', $column->comment);
	if(in_array('trigger[delete]', $commentArray)) {
		$publicAttribute = $name.'_i';
		if(!in_array($publicAttribute, $publicVariable)) {
			echo "\tpublic \${$publicAttribute};\n";
			$publicVariable[] = $publicAttribute;
		}
		$i18n = 1;
	}
endforeach;
//echo '<pre>';
//print_r($labels);
foreach($labels as $name=>$label):
	if(in_array($name, array('tag_id'))) {
		$relationArray = explode('_', $name);
		$relationName = $relationArray[0];

		$publicAttribute = $relationName.'_i';
		if(!in_array($publicAttribute, $publicVariable)) {
			echo "\tpublic \${$publicAttribute};\n";
			$publicVariable[] = $publicAttribute;
		}
	}
endforeach;
foreach($columns as $name=>$column):
	if(!(in_array($name, array('creation_id','modified_id','user_id','updated_id','member_id','tag_id'))) && $column->dbType == 'text' && $column->comment == 'file') {
		$publicAttribute = 'old_'.$name.'_i';
		if(!in_array($publicAttribute, $publicVariable)) {
			echo "\tpublic \${$publicAttribute};\n";
		}
	}
endforeach; ?>

	// Variable Search
<?php 
//echo '<pre>';
//print_r($columns);
foreach($columns as $name=>$column):
	if($column->isForeignKey == '1') {
		$relationName = setRelationName($name, true);
		if($relationName == 'cat')
			$relationName = 'category';

		$publicAttribute = $relationName.'_search';
		if($publicAttribute != 'category_search' && !in_array($publicAttribute, $publicVariable)) {
			echo "\tpublic \${$publicAttribute};\n";
			$publicVariable[] = $publicAttribute;
		}
	}
endforeach;
foreach($labels as $name=>$label):
	if(in_array($name, array('creation_id','modified_id','user_id','updated_id','member_id','tag_id'))) {
		$relationArray = explode('_', $name);
		$relationName = $relationArray[0];

		$publicAttribute = $relationName.'_search';
		if(!in_array($publicAttribute, $publicVariable)) {
			echo "\tpublic \${$publicAttribute};\n";
			$publicVariable[] = $publicAttribute;
		}
	}
endforeach; ?>
<?php if($slugCondition) {?>

	/**
	 * Behaviors for this model
	 */
	public function behaviors() 
	{
		return array(
			'sluggable' => array(
				'class'=>'ext.yii-behavior-sluggable.SluggableBehavior',
				'columns' => array('title.message'),
				'unique' => true,
				'update' => true,
			),
		);
	}
<?php }?>

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return <?php echo $modelClass; ?> the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
<?php if($connectionId!='db'):?>

	/**
	 * @return CDbConnection the database connection used for this class
	 */
	public function getDbConnection()
	{
		return Yii::app()-><?php echo $connectionId ?>;
	}
<?php endif?>

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		preg_match("/dbname=([^;]+)/i", $this->dbConnection->connectionString, $matches);
		return $matches[1].'.<?php echo $tableName; ?>';
	}
<?php if($tableName[0] == '_') {?>

	/**
	 * @return string the primarykey column
	 */
	public function primaryKey()
	{
		return '<?php echo $primaryKeyColumn; ?>';
	}
<?php }?>

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
<?php 
//print_r($rules);
foreach($rules as $rule): ?>
			<?php echo $rule.",\n"; ?>
<?php endforeach;
if($i18n):
	foreach($columns as $name=>$column):
		$commentArray = explode(',', $column->comment);
		if(in_array('trigger[delete]', $commentArray)):
			$publicAttribute = $name.'_i';
			$maxlength = in_array('redactor', $commentArray) ? '~' : (in_array('text', $commentArray) ? '128' : '32');
			if($maxlength != '~'):?>
			array('<?php echo $publicAttribute;?>', 'length', 'max'=><?php echo $maxlength;?>),
<?php 		endif;
		endif;
	endforeach;
endif;?>
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('<?php echo implode(', ', array_merge(array_keys($columns), $publicVariable)); ?>', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
<?php 
//echo '<pre>';
//print_r($relations);
$availableRelations = array();
foreach($relations as $name=>$relation): ?>
			<?php
			$relationName = setRelationName($name);
			if($relationName == 'cat')
				$relationName = 'category';
			if(preg_match('/Core/', $relation))
				$relationModel = preg_replace('(Core)', '', $relation);
			else
				$relationModel = preg_replace('(Ommu)', '', $relation);
			if(!in_array($relationName, $availableRelations)) {
				echo "'$relationName' => $relationModel,\n";
				$availableRelations[] = $relationName;
	 		} ?>
<?php endforeach;
if($i18n):
	foreach($columns as $name=>$column):
		$commentArray = explode(',', $column->comment);
		if(in_array('trigger[delete]', $commentArray)):
			$publicAttributeRelation = preg_match('/(name|title)/', $name) ? 'title' : (preg_match('/(desc|description)/', $name) ? ($name != 'description' ? 'description' : $name.'Rltn') : $name.'Rltn');
			if(!in_array($publicAttributeRelation, $availableRelations)) {
				echo "\t\t\t'$publicAttributeRelation' => array(self::BELONGS_TO, 'SourceMessage', '{$name}'),\n";
				$availableRelations[] = $publicAttributeRelation;
			}
		endif;
	endforeach;
endif;
	foreach($columns as $name=>$column):
		if(in_array($name, array('creation_id','modified_id','user_id','updated_id','member_id'))) {
			$relationArray = explode('_', $name);
			$relationName = $relationArray[0];
			if(!in_array($relationName, $availableRelations)) {
				if($name == 'member_id')
					echo "\t\t\t'$relationName' => array(self::BELONGS_TO, 'Members', '{$name}'),\n";
				else
					echo "\t\t\t'$relationName' => array(self::BELONGS_TO, 'Users', '{$name}'),\n";
				$availableRelations[] = $relationName;
			}
		} else if($name == 'tag_id') {
			$relationArray = explode('_', $name);
			$relationName = $relationArray[0];
			if(!in_array($relationName, $availableRelations)) {
				echo "\t\t\t'$relationName' => array(self::BELONGS_TO, 'OmmuTags', '{$name}'),\n";
				$availableRelations[] = $relationName;
			}
		}
	endforeach;?>
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
<?php 
$publicAttributes = array();
foreach($labels as $name=>$label):
	if(strtolower($label) == 'cat')
		$label = 'Category';
		
	if(!in_array($name, $publicAttributes)) {
		echo "\t\t\t'$name' => Yii::t('attribute', '$label'),\n";
		$publicAttributes[] = $name;
	}
endforeach;
foreach($columns as $name=>$column):
	$commentArray = explode(',', $column->comment);
	if(in_array('trigger[delete]', $commentArray)) {
		$publicAttribute = $name.'_i';
		
		if(!in_array($publicAttribute, $publicAttributes)) {
			$publicAttributeLabel = ucwords(strtolower($name));
			echo "\t\t\t'$publicAttribute' => Yii::t('attribute', '$publicAttributeLabel'),\n";
			$publicAttributes[] = $publicAttribute;
		}
	}
endforeach;
foreach($columns as $name=>$column):
	if($column->isForeignKey == '1') {
		$relationName = setRelationName($name, true);
		if($relationName == 'cat')
			$relationName = 'category';
		
		$publicAttribute = $relationName.'_search';
		if($publicAttribute != 'category_search' && !in_array($publicAttribute, $publicAttributes)) {
			$publicAttributeLabel = ucwords(strtolower($relationName));
			echo "\t\t\t'$publicAttribute' => Yii::t('attribute', '$publicAttributeLabel'),\n";
			$publicAttributes[] = $publicAttribute;
		}
	}
endforeach;
foreach($columns as $name=>$column):
	if($column->dbType == 'text' && $column->comment == 'file') {
		$publicAttribute = 'old_'.$name.'_i';
		if(!in_array($publicAttribute, $publicAttributes)) {
			$publicAttributeLabel = ucwords(strtolower('old '.$name));
			echo "\t\t\t'$publicAttribute' => Yii::t('attribute', '$publicAttributeLabel'),\n";
		}
	}
endforeach;
foreach($labels as $name=>$label):
	if(in_array($name, array('creation_id','modified_id','user_id','updated_id','member_id','tag_id'))) {
		$relationArray = explode('_', $name);
		$relationName = $relationArray[0];
		$publicAttribute = $relationName.'_search';
		
		if(!in_array($publicAttribute, $publicAttributes)) {
			echo "\t\t\t'$publicAttribute' => Yii::t('attribute', '$label'),\n";
			$publicAttributes[] = $publicAttribute;
		}
	}
endforeach; ?>
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

<?php
//echo '<pre>';
//print_r($columns);
$isPrimaryKey = '';
$isVariableSearch = 0;
$publicAttributes = array();

foreach($columns as $name=>$column) {	
	if($column->isForeignKey == '1' || (in_array($name, array('creation_id','modified_id','user_id','updated_id','member_id'))))
		$isVariableSearch = 1;
}
if($isVariableSearch == 1) {?>
		// Custom Search
		$criteria->with = array(
<?php foreach($columns as $name=>$column) {	
	if($column->isForeignKey == '1') {
		$relationName = setRelationName($name, true);
		if($relationName == 'cat')
			$relationName = 'category';
			
		$relationAttribute = 'column_name_relation';
		if($relationName == 'user')
			$relationAttribute = 'displayname';
		
		if($relationName != 'category' && !in_array($relationName, $publicAttributes)) {
			echo "\t\t\t'$relationName' => array(\n";
			echo "\t\t\t\t'alias'=>'$relationName',\n";
			echo "\t\t\t\t'select'=>'$relationAttribute',\n";
			echo "\t\t\t),\n";
			$publicAttributes[] = $relationName;
		}
	}
}
if($i18n):
foreach($columns as $name=>$column):
	$commentArray = explode(',', $column->comment);
	if(in_array('trigger[delete]', $commentArray)):
		$publicAttributeRelation = preg_match('/(name|title)/', $name) ? 'title' : (preg_match('/(desc|description)/', $name) ? ($name != 'description' ? 'description' : $name.'Rltn') : $name.'Rltn');
		
		if(!in_array($publicAttributeRelation, $publicAttributes)) {
			echo "\t\t\t'$publicAttributeRelation' => array(\n";
			echo "\t\t\t\t'alias'=>'$publicAttributeRelation',\n";
			echo "\t\t\t\t'select'=>'message',\n";
			echo "\t\t\t),\n";
			$publicAttributes[] = $publicAttributeRelation;
		}
	endif;
endforeach;
endif;
foreach($columns as $name=>$column) {
	if(in_array($name, array('creation_id','modified_id','user_id','updated_id','member_id'))) {
		$relationArray = explode('_',$name);
		$relationName = $relationArray[0];

		if(!in_array($relationName, $publicAttributes)) {
			if($name == 'member_id') {
				$relationName = 'member_view';
				echo "\t\t\t'{$relationName}.view' => array(\n";
				echo "\t\t\t\t'alias'=>'{$relationName}_view',\n";
				echo "\t\t\t\t'select'=>'member_name',\n";
				echo "\t\t\t),\n";
			} else {
				echo "\t\t\t'$relationName' => array(\n";
				echo "\t\t\t\t'alias'=>'$relationName',\n";
				echo "\t\t\t\t'select'=>'displayname',\n";
				echo "\t\t\t),\n";
			}
			$publicAttributes[] = $relationName;
		}
	} else if($name == 'tag_id') {
		$relationArray = explode('_',$name);
		$relationName = $relationArray[0];
		
		if(!in_array($relationName, $publicAttributes)) {
			echo "\t\t\t'$relationName' => array(\n";
			echo "\t\t\t\t'alias'=>'$relationName',\n";
			echo "\t\t\t\t'select'=>'body',\n";
			echo "\t\t\t),\n";
			$publicAttributes[] = $relationName;
		}
	}
}?>
		);

<?php }
/*
foreach($columns as $name=>$column) {	
	if($column->isForeignKey == '1' || (in_array($column->name, array('creation_id','modified_id','user_id','updated_id','member_id')))) {
		$arrayName = explode('_', $column->name);
		$cName = 'displayname';
		if($column->isForeignKey == '1')
			$cName = 'column_name_relation';
		$cRelation = $arrayName[0];
		if($cRelation == 'cat')
			$cRelation = 'category';
		if($column->name == 'member_id') {
			$cRelation = 'member_view';
			$cName = 'member_name';	
		}
		$name = $cRelation.'_search';
		echo "\t\t\$criteria->compare('{$cRelation}.{$cName}',strtolower(\$this->$name),true);\n";
	}
}
echo '<pre>';
print_r($columns);
echo $tableName[0];
*/
foreach($columns as $name=>$column) {
	if($column->name == 'publish') {
		echo "\t\tif(Yii::app()->getRequest()->getParam('type') == 'publish')\n";
		echo "\t\t\t\$criteria->compare('t.$name', 1);\n";
		echo "\t\telseif(Yii::app()->getRequest()->getParam('type') == 'unpublish')\n";
		echo "\t\t\t\$criteria->compare('t.$name', 0);\n";
		echo "\t\telseif(Yii::app()->getRequest()->getParam('type') == 'trash')\n";
		echo "\t\t\t\$criteria->compare('t.$name', 2);\n";
		echo "\t\telse {\n";
		echo "\t\t\t\$criteria->addInCondition('t.$name', array(0,1));\n";
		echo "\t\t\t\$criteria->compare('t.$name', \$this->$name);\n";
		echo "\t\t}\n";

	} else if($column->isForeignKey == '1' || (in_array($name, array('creation_id','modified_id','user_id','updated_id','member_id','tag_id')))) {
		$relationArray = explode('_',$name);
		$relationName = $relationArray[0];
		if($relationName == 'cat')
			$relationName = 'category';
		echo "\t\t\$criteria->compare('t.$name', Yii::app()->getRequest()->getParam('$relationName') ? Yii::app()->getRequest()->getParam('$relationName') : \$this->$name);\n";

	} else if(in_array($column->dbType, array('timestamp','datetime'))) {
		echo "\t\tif(\$this->$name != null && !in_array(\$this->$name, array('0000-00-00 00:00:00', '1970-01-01 00:00:00')))\n";
		echo "\t\t\t\$criteria->compare('date(t.$name)', date('Y-m-d', strtotime(\$this->$name)));\n";

	} else if(in_array($column->dbType, array('date'))) {
		echo "\t\tif(\$this->$name != null && !in_array(\$this->$name, array('0000-00-00', '1970-01-01')))\n";
		echo "\t\t\t\$criteria->compare('date(t.$name)', date('Y-m-d', strtotime(\$this->$name)));\n";

	} else if(in_array($column->dbType, array('int','smallint')) || ($column->type==='string' && $column->isPrimaryKey == '1'))
		echo "\t\t\$criteria->compare('t.$name', \$this->$name);\n";

	else if($column->type==='string') {
		if(preg_match('/(int)/', $column->dbType) || ($tableName[0] == '_' && preg_match('/(decimal)/', $column->dbType)))
			echo "\t\t\$criteria->compare('t.$name', \$this->$name);\n";
		else
			echo "\t\t\$criteria->compare('t.$name', strtolower(\$this->$name), true);\n";

	} else
		echo "\t\t\$criteria->compare('t.$name', \$this->$name);\n";

	if($column->isPrimaryKey) {
		$isPrimaryKey = $name;
	}
}
$publicAttributes = array();
if($isVariableSearch == 1)
	echo "\n";
$publicAttributes = array();
foreach($columns as $name=>$column) {	
	if($column->isForeignKey == '1') {
		$relationName = setRelationName($name, true);
		if($relationName == 'cat')
			$relationName = 'category';

		$relationAttribute = 'column_name_relation';
		if($relationName == 'user')
			$relationAttribute = 'displayname';
			
		$publicAttribute = $relationName.'_search';
		if($publicAttribute != 'category_search' && !in_array($publicAttribute, $publicAttributes)) {
			echo "\t\t\$criteria->compare('{$relationName}.{$relationAttribute}', strtolower(\$this->$publicAttribute), true);\n";
			$publicAttributes[] = $publicAttribute;
		}
	}
}
if($i18n):
foreach($columns as $name=>$column):
	$commentArray = explode(',', $column->comment);
	if(in_array('trigger[delete]', $commentArray)):
		$publicAttribute = $name.'_i';
		$publicAttributeRelation = preg_match('/(name|title)/', $name) ? 'title' : (preg_match('/(desc|description)/', $name) ? ($name != 'description' ? 'description' : $name.'Rltn') : $name.'Rltn');

		if(!in_array($publicAttribute, $publicAttributes)) {
			echo "\t\t\$criteria->compare('{$publicAttributeRelation}.message', strtolower(\$this->$publicAttribute), true);\n";
			$publicAttributes[] = $publicAttribute;
		}
	endif;
endforeach;
endif;
foreach($columns as $name=>$column) {
	if(in_array($name, array('creation_id','modified_id','user_id','updated_id','member_id'))) {
		$relationArray = explode('_',$name);
		$relationName = $relationArray[0];
		$publicAttribute = $relationName.'_search';
		$relationAttribute = 'displayname';

		if($column->name == 'member_id') {
			$relationName = 'member_view';
			$relationAttribute = 'member_name';
		}
		if(!in_array($publicAttribute, $publicAttributes)) {
			echo "\t\t\$criteria->compare('{$relationName}.{$relationAttribute}', strtolower(\$this->$publicAttribute), true);\n";
			$publicAttributes[] = $publicAttribute;
		}
	} else if($name == 'tag_id') {
		$relationArray = explode('_',$name);
		$relationName = $relationArray[0];
		$publicAttribute = $relationName.'_search';
		$relationAttribute = 'body';

		if(!in_array($publicAttribute, $publicAttributes)) {
			echo "\t\t\$$publicAttribute = Utility::getUrlTitle(strtolower(trim(\$this->$publicAttribute)));\n";
			echo "\t\t\$criteria->compare('$relationName.$relationAttribute', \$$publicAttribute, true);\n";
			$publicAttributes[] = $publicAttribute;
		}
	}
}

	if($tableName[0] == '_' && !$isPrimaryKey)
		$isPrimaryKey = $primaryKeyColumn;

	echo "\n\t\tif(!(Yii::app()->getRequest()->getParam('{$modelClass}_sort')))\n";
	echo "\t\t\t\$criteria->order = 't.$isPrimaryKey DESC';\n";
?>

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize'=>Yii::app()->params['grid-view'] ? Yii::app()->params['grid-view']['pageSize'] : 50,
			),
		));
	}

	/**
	 * Set default columns to display
	 */
	protected function afterConstruct() {
		if(count($this->templateColumns) == 0) {
			$this->templateColumns['_option'] = array(
				'class' => 'CCheckBoxColumn',
				'name' => 'id',
				'selectableRows' => 2,
				'checkBoxHtmlOptions' => array('name' => 'trash_id[]')
			);
			$this->templateColumns['_no'] = array(
				'header' => Yii::t('app', 'No'),
				'value' => '$this->grid->dataProvider->pagination->currentPage*$this->grid->dataProvider->pagination->pageSize + $row+1',
				'htmlOptions' => array(
					'class' => 'center',
				),
			);
<?php
//echo '<pre>';
//print_r($columns);
foreach($columns as $name=>$column)
{
	if(!$column->isPrimaryKey && $column->dbType != 'tinyint(1)') {
		if($column->isForeignKey == '1' || (in_array($column->name, array('creation_id','modified_id','user_id','updated_id','member_id','tag_id')))) {
			$arrayName = explode('_', $column->name);
			$relationName = $arrayName[0];
			$columnName = 'displayname';
			if($column->isForeignKey == '1')
				$columnName = 'column_name_relation';
			if($column->name == 'tag_id')
				$columnName = 'body';
			if($relationName == 'cat')
				$relationName = 'category';
			if($column->name == 'member_id') {
				$relationName = 'member_view';
				$columnName = 'member_name';
			}
			$publicAttribute = $relationName.'_search';
			if($relationName == 'category')
				$publicAttribute = $column->name;
				
			echo "\t\t\tif(!Yii::app()->getRequest()->getParam('$relationName')) {\n";
			echo "\t\t\t\t\$this->templateColumns['$publicAttribute'] = array(\n";
			echo "\t\t\t\t\t'name' => '$publicAttribute',\n";
if($column->name == 'tag_id') {
			echo "\t\t\t\t\t'value' => 'str_replace(\'-\', \' \', \$data->{$relationName}->{$columnName})',\n";
} else {
			echo "\t\t\t\t\t'value' => '\$data->{$relationName}->{$columnName} ? \$data->{$relationName}->{$columnName} : \'-\'',\n";
}
			echo "\t\t\t\t);\n";
			echo "\t\t\t}\n";
			
		} else if(in_array($column->dbType, array('timestamp','datetime','date'))) {
			echo "\t\t\t\$this->templateColumns['$name'] = array(\n";
			echo "\t\t\t\t'name' => '$name',\n";
			if(in_array($column->dbType, array('timestamp','datetime')))
				echo "\t\t\t\t'value' => '!in_array(\$data->$name, array(\'0000-00-00 00:00:00\', \'1970-01-01 00:00:00\')) ? Utility::dateFormat(\$data->$name) : \'-\'',\n";
			else
				echo "\t\t\t\t'value' => '!in_array(\$data->$name, array(\'0000-00-00\', \'1970-01-01\')) ? Utility::dateFormat(\$data->$name) : \'-\'',\n";
			echo "\t\t\t\t'htmlOptions' => array(\n";
			echo "\t\t\t\t\t'class' => 'center',\n";
			echo "\t\t\t\t),\n";
if($this->datepickerStatus == '0') {
	echo "\t\t\t\t'filter' => 'native-datepicker',\n";
	echo "\t\t\t\t/*\n";
} else {
	echo "\t\t\t\t//'filter' => 'native-datepicker',\n";
}
			echo "\t\t\t\t'filter' => Yii::app()->controller->widget('application.libraries.core.components.system.CJuiDatePicker', array(\n";
			echo "\t\t\t\t\t'model'=>\$this,\n";
			echo "\t\t\t\t\t'attribute'=>'$name',\n";
			echo "\t\t\t\t\t'language' => 'en',\n";
			echo "\t\t\t\t\t'i18nScriptFile' => 'jquery-ui-i18n.min.js',\n";
			echo "\t\t\t\t\t//'mode'=>'datetime',\n";
			echo "\t\t\t\t\t'htmlOptions' => array(\n";
			echo "\t\t\t\t\t\t'id' => '$name";echo "_filter',\n";
			echo "\t\t\t\t\t\t'on_datepicker' => 'on',\n";
			echo "\t\t\t\t\t\t'placeholder' => Yii::t('phrase', 'filter'),\n";
			echo "\t\t\t\t\t),\n";
			echo "\t\t\t\t\t'options'=>array(\n";
			echo "\t\t\t\t\t\t'showOn' => 'focus',\n";
			echo "\t\t\t\t\t\t'dateFormat' => 'yy-mm-dd',\n";
			echo "\t\t\t\t\t\t'showOtherMonths' => true,\n";
			echo "\t\t\t\t\t\t'selectOtherMonths' => true,\n";
			echo "\t\t\t\t\t\t'changeMonth' => true,\n";
			echo "\t\t\t\t\t\t'changeYear' => true,\n";
			echo "\t\t\t\t\t\t'showButtonPanel' => true,\n";
			echo "\t\t\t\t\t),\n";
			echo "\t\t\t\t), true),\n";
if($this->datepickerStatus == '0') {
			echo "\t\t\t\t*/\n";
}
			echo "\t\t\t);\n";
			
		} else {
			$translateCondition = 0;
			$commentArray = explode(',', $column->comment);
			$publicAttribute = $name;
			if(in_array('trigger[delete]', $commentArray)) {
				$publicAttribute = $name.'_i';
				$publicAttributeRelation = preg_match('/(name|title)/', $name) ? 'title' : (preg_match('/(desc|description)/', $name) ? ($name != 'description' ? 'description' : $name.'Rltn') : $name.'Rltn');
				$translateCondition = 1;
			}
			echo "\t\t\t\$this->templateColumns['$publicAttribute'] = array(\n";
			echo "\t\t\t\t'name' => '$publicAttribute',\n";
if($translateCondition)
			echo "\t\t\t\t'value' => '\$data->{$publicAttributeRelation}->message',\n";
else {
	if($column->dbType == 'text' && $column->comment == 'file') {
		if($this->uploadPathSubfolderStatus):
			$CHtml = "CHtml::link(\$data->$name, Yii::app()->request->baseUrl.\'/public/banner/\'.\$data->$primaryKeyColumn.\'/\'.\$data->$name, array(\'target\' => \'_blank\'))";
		else:
			$CHtml = "CHtml::link(\$data->$name, Yii::app()->request->baseUrl.\'/public/banner/\'.\$data->$name, array(\'target\' => \'_blank\'))";
		endif;
			echo "\t\t\t\t'value' => '\$data->$name ? $CHtml : \'-\'',\n";
	} else
			echo "\t\t\t\t'value' => '\$data->$name',\n";
}
//if(in_array($column->dbType, array('text'))) {
//			echo "\t\t\t\t'type' => 'raw',\n";
//}
			echo "\t\t\t);\n";
		}
	}
}
foreach($columns as $name=>$column)
{
	if(!$column->isPrimaryKey && $column->dbType == 'tinyint(1)') {
		if(in_array($name, array('publish')))
			echo "\t\t\tif(!Yii::app()->getRequest()->getParam('type')) {\n";
		echo "\t\t\t\$this->templateColumns['$name'] = array(\n";
		echo "\t\t\t\t'name' => '$name',\n";
		if($column->comment != '')
			echo "\t\t\t\t'value' => 'Utility::getPublish(Yii::app()->controller->createUrl(\'$name\', array(\'id\'=>\$data->$isPrimaryKey)), \$data->$name, \'$column->comment\')',\n";
		else
			echo "\t\t\t\t'value' => 'Utility::getPublish(Yii::app()->controller->createUrl(\'$name\', array(\'id\'=>\$data->$isPrimaryKey)), \$data->$name)',\n";
		echo "\t\t\t\t'htmlOptions' => array(\n";
		echo "\t\t\t\t\t'class' => 'center',\n";
		echo "\t\t\t\t),\n";
		echo "\t\t\t\t'filter'=>array(\n";
		echo "\t\t\t\t\t1=>Yii::t('phrase', 'Yes'),\n";
		echo "\t\t\t\t\t0=>Yii::t('phrase', 'No'),\n";
		echo "\t\t\t\t),\n";
		echo "\t\t\t\t'type' => 'raw',\n";
		echo "\t\t\t);\n";
		if(in_array($name, array('publish')))
			echo "\t\t\t}\n";
	}
}
?>
		}
		parent::afterConstruct();
	}

	/**
	 * User get information
	 */
	public static function getInfo($id, $column=null)
	{
		if($column != null) {
			$model = self::model()->findByPk($id, array(
				'select' => $column
			));
			return $model->$column;
			
		} else {
			$model = self::model()->findByPk($id);
			return $model;
		}
	}
<?php 
if($this->createFunctionStatus) {?>

	/**
	 * get<?php echo ucfirst(setRelationName($modelClass))."\n";?>
	 */
	public static function get<?php echo ucfirst(setRelationName($modelClass));?>(<?php echo $publishCondition ? '$publish=null, $array=true' : '$array=true';?>) 
	{
		$criteria=new CDbCriteria;
<?php if($publishCondition):?>
		if($publish != null)
			$criteria->compare('t.publish', $publish);

<?php endif;?>
		$model = self::model()->findAll($criteria);

		if($array == true) {
			$items = array();
			if($model != null) {
				foreach($model as $key => $val) {
<?php 
$attribute = guessNameColumn($columns);
if($i18n):
	$i18nRelation = preg_match('/(name|title)/', $attribute) ? 'title' : '';?>
					$items[$val-><?php echo $isPrimaryKey;?>] = $val-><?php echo $i18nRelation ? $i18nRelation.'->message' : $attribute;?>;
<?php else:?>
					$items[$val-><?php echo $isPrimaryKey;?>] = $val-><?php echo $attribute;?>;
<?php endif;?>
				}
				return $items;
			} else
				return false;
		} else
			return $model;
	}
<?php }
if($i18n) {?>

	/**
	 * This is invoked when a record is populated with data from a find() call.
	 */
	protected function afterFind()
	{
<?php
foreach($columns as $name=>$column):
	$commentArray = explode(',', $column->comment);
	if(in_array('trigger[delete]', $commentArray)):
		$publicAttribute = $name.'_i';
		$publicAttributeRelation = preg_match('/(name|title)/', $name) ? 'title' : (preg_match('/(desc|description)/', $name) ? ($name != 'description' ? 'description' : $name.'Rltn') : $name.'Rltn');?>
		$this-><?php echo $publicAttribute;?> = $this-><?php echo $publicAttributeRelation;?>->message;
<?php endif;
endforeach; ?>
		
		parent::afterFind();
	}
<?php }
if(!($tableName[0] == '_')) {?>

	/**
	 * before validate attributes
	 */
	protected function beforeValidate() 
	{
		if(parent::beforeValidate()) {
<?php
$creationCondition = 0;
foreach($columns as $name=>$column)
{
	if(in_array($name, array('creation_id','modified_id','updated_id')) && $column->comment != 'trigger') {
		if($name == 'creation_id') {
			$creationCondition = 1;
			echo "\t\t\tif(\$this->isNewRecord)\n";
			echo "\t\t\t\t\$this->{$name} = !Yii::app()->user->isGuest ? Yii::app()->user->id : null;\n";
		} else {
			if($creationCondition)
				echo "\t\t\telse\n";
			else
				echo "\t\t\tif(!\$this->isNewRecord)\n";
			echo "\t\t\t\t\$this->{$name} = !Yii::app()->user->isGuest ? Yii::app()->user->id : null;\n";
		}
	}
}
if($uploadCondition) {
	foreach($columns as $name=>$column)
	{
		if($column->dbType == 'text' && $column->comment == 'file') {?>

			$<?php echo $name;?>FileType = array('bmp','gif','jpg','png');
			$<?php echo $name;?> = CUploadedFile::getInstance($this, '<?php echo $name;?>');
			if($<?php echo $name;?>->name != null) {
				$extension = pathinfo($<?php echo $name;?>->name, PATHINFO_EXTENSION);
				if(!in_array(strtolower($extension), $<?php echo $name;?>FileType))
					$this->addError('<?php echo $name;?>', 'The file {name} cannot be uploaded. Only files with these extensions are allowed: {extensions}', array(
						'{name}'=>$<?php echo $name;?>->name,
						'{extensions}'=>Utility::formatFileType($<?php echo $name;?>FileType, false),
					));
			} /* else {
				//if($this->isNewRecord && $controller == 'o/media')
					$this->addError('<?php echo $name;?>', Yii::t('phrase', '{attribute} cannot be blank.', array('{attribute}'=>$this->getAttributeLabel('<?php echo $name;?>'))));
			} */
<?php 	}
	}
}?>
			// Create action
		}
		return true;
	}

	/**
	 * after validate attributes
	 */
	protected function afterValidate()
	{
		parent::afterValidate();
		// Create action
		
		return true;
	}

<?php }
if(!($tableName[0] == '_')) {?>
	/**
	 * before save attributes
	 */
	protected function beforeSave() 
	{
<?php if($i18n) {?>
		$module = strtolower(Yii::app()->controller->module->id);
		$controller = strtolower(Yii::app()->controller->id);
		$action = strtolower(Yii::app()->controller->action->id);

		$location = $module.' '.$controller;
		
<?php }?>
		if(parent::beforeSave()) {
<?php 
if($uploadCondition) {?>
			if(!$this->isNewRecord) {
<?php if($this->uploadPathSubfolderStatus) {?>
				$<?php echo $this->uploadPathNameSource;?> = '<?php echo $this->uploadPathDirectorySource;?>/'.$this-><?php echo $primaryKeyColumn; ?>;
<?php } else {?>
				$<?php echo $this->uploadPathNameSource;?> = '<?php echo $this->uploadPathDirectorySource;?>';
<?php }?>
				$verwijderen_path = join('/', array($<?php echo $this->uploadPathNameSource;?>, 'verwijderen'));
				// Add directory
				if(!file_exists($<?php echo $this->uploadPathNameSource;?>) || !file_exists($verwijderen_path)) {
					@mkdir($<?php echo $this->uploadPathNameSource;?>, 0755, true);
					@mkdir($verwijderen_path, 0755, true);

					// Add file in directory (index.php)
					$newFile = $<?php echo $this->uploadPathNameSource;?>.'/index.php';
					$FileHandle = fopen($newFile, 'w');

					$newVerwijderenFile = $verwijderen_path.'/index.php';
					$FileHandle = fopen($newVerwijderenFile, 'w');
				} else {
					@chmod($<?php echo $this->uploadPathNameSource;?>, 0755, true);
					@chmod($verwijderen_path, 0755, true);
				}
<?php foreach($columns as $name=>$column) {
	if($column->dbType == 'text' && $column->comment == 'file') {?>

				$this-><?php echo $name;?> = CUploadedFile::getInstance($this, '<?php echo $name;?>');
				if($this-><?php echo $name;?> != null) {
					if($this-><?php echo $name;?> instanceOf CUploadedFile) {
						$fileName = time().'_'.$this-><?php echo $primaryKeyColumn;?>.'.'.strtolower($this-><?php echo $name;?>->extensionName);
						if($this-><?php echo $name;?>->saveAs($<?php echo $this->uploadPathNameSource;?>.'/'.$fileName)) {
							if($this->old_<?php echo $name;?>_i != '' && file_exists($<?php echo $this->uploadPathNameSource;?>.'/'.$this->old_<?php echo $name;?>_i))
								rename($<?php echo $this->uploadPathNameSource;?>.'/'.$this->old_<?php echo $name;?>_i, 'public/banner/verwijderen/'.$this-><?php echo $primaryKeyColumn;?>.'_'.$this->old_<?php echo $name;?>_i);
							$this-><?php echo $name;?> = $fileName;
						}
					}
				} else {
					if($this-><?php echo $name;?> == '')
						$this-><?php echo $name;?> = $this->old_<?php echo $name;?>_i;
				}
<?php }
}?>
			}

<?php }

foreach($columns as $name=>$column)
{
	if(in_array($column->dbType, array('date','datetime')) && $column->comment != 'trigger') {
		$datetimeType = $column->dbType == 'date' ? 'Y-m-d' : 'Y-m-d';	//Y-m-d H:i:s
		echo "\t\t\t\$this->$name = date('$datetimeType', strtotime(\$this->$name));\n";
	} else if($column->dbType == 'text' && $column->comment == 'serialize') {
		echo "\t\t\t\$this->$name = serialize(\$this->$name);\n";

	} else if($column->name == 'tag_id') {
		$relationArray = explode('_', $name);
		$relationName = $relationArray[0];
		$publicAttribute = $relationName.'_i';?>
			if($this->isNewRecord) {
				$<?php echo $publicAttribute;?> = Utility::getUrlTitle(strtolower(trim($this-><?php echo $publicAttribute;?>)));
				if($this-><?php echo $column->name;?> == 0) {
					$<?php echo $relationName;?> = OmmuTags::model()->find(array(
						'select' => '<?php echo $column->name;?>, body',
						'condition' => 'body = :body',
						'params' => array(
							':body' => $<?php echo $publicAttribute;?>,
						),
					));
					if($<?php echo $relationName;?> != null)
						$this-><?php echo $column->name;?> = $<?php echo $relationName;?>-><?php echo $column->name;?>;
					else {
						$data = new OmmuTags;
						$data->body = $this-><?php echo $publicAttribute;?>;
						if($data->save())
							$this-><?php echo $column->name;?> = $data-><?php echo $column->name;?>;
					}
				}
			}
<?php } else {
		$commentArray = explode(',', $column->comment);
		if(in_array('trigger[delete]', $commentArray)) {
			$publicAttribute = $name.'_i';
			$publicAttributeLocation = preg_match('/(name|title)/', $name) ? '_title' : (preg_match('/(desc|description)/', $name) ? ($name != 'description' ? '_description' : '_'.$name) : '_'.$name);?>
			if($this->isNewRecord || (!$this->isNewRecord && !$this-><?php echo $name;?>)) {
				$<?php echo $name;?>=new SourceMessage;
				$<?php echo $name;?>->message = $this-><?php echo $publicAttribute;?>;
				$<?php echo $name;?>->location = $location.'<?php echo $publicAttributeLocation;?>';
				if($<?php echo $name;?>->save())
					$this-><?php echo $name;?> = $<?php echo $name;?>->id;
<?php if($i18n && preg_match('/(name|title)/', $name)) {?>

				$this->slug = Utility::getUrlTitle($this-><?php echo $publicAttribute;?>);
<?php }?>
				
			} else {
				$<?php echo $name;?> = SourceMessage::model()->findByPk($this-><?php echo $name;?>);
				$<?php echo $name;?>->message = $this-><?php echo $publicAttribute;?>;
				$<?php echo $name;?>->save();
			}

<?php	}
	}
} ?>
			// Create action
		}
		return true;
	}

<?php }
if(!($tableName[0] == '_')) {?>
	/**
	 * After save attributes
	 */
	protected function afterSave() 
	{
		parent::afterSave();
<?php 
if($uploadCondition) {
	if($this->uploadPathSubfolderStatus) {?>

		$<?php echo $this->uploadPathNameSource;?> = '<?php echo $this->uploadPathDirectorySource;?>/'.$this-><?php echo $primaryKeyColumn; ?>;
<?php } else {?>

		$<?php echo $this->uploadPathNameSource;?> = '<?php echo $this->uploadPathDirectorySource;?>';
<?php }?>
		$verwijderen_path = join('/', array($<?php echo $this->uploadPathNameSource;?>, 'verwijderen'));
		// Add directory
		if(!file_exists($<?php echo $this->uploadPathNameSource;?>) || !file_exists($verwijderen_path)) {
			@mkdir($<?php echo $this->uploadPathNameSource;?>, 0755, true);
			@mkdir($verwijderen_path, 0755,true);

			// Add file in directory (index.php)
			$newFile = $<?php echo $this->uploadPathNameSource;?>.'/index.php';
			$FileHandle = fopen($newFile, 'w');
			
			$newVerwijderenFile = $verwijderen_path.'/index.php';
			$FileHandle = fopen($newVerwijderenFile, 'w');
		} else {
			@chmod($<?php echo $this->uploadPathNameSource;?>, 0755, true);
			@chmod($verwijderen_path, 0755,true);
		}

		if($this->isNewRecord) {
<?php foreach($columns as $name=>$column)
{
	if($column->dbType == 'text' && $column->comment == 'file') {?>
			$this-><?php echo $name;?> = CUploadedFile::getInstance($this, '<?php echo $name;?>');
			if($this-><?php echo $name;?> != null) {
				if($this-><?php echo $name;?> instanceOf CUploadedFile) {
					$fileName = time().'_'.$this-><?php echo $primaryKeyColumn;?>.'.'.strtolower($this-><?php echo $name;?>->extensionName);
					if($this-><?php echo $name;?>->saveAs($<?php echo $this->uploadPathNameSource;?>.'/'.$fileName))
						self::model()->updateByPk($this-><?php echo $primaryKeyColumn;?>, array('<?php echo $name;?>'=>$fileName));
				}
			}
			
<?php }
}?>
		}
<?php }?>
		// Create action
	}

	/**
	 * Before delete attributes
	 */
	protected function beforeDelete() 
	{
		if(parent::beforeDelete()) {
			// Create action
		}
		return true;
	}

	/**
	 * After delete attributes
	 */
	protected function afterDelete() 
	{
		parent::afterDelete();
		
		//delete article image
<?php if($uploadCondition) {
	if($this->uploadPathSubfolderStatus) {?>
		$<?php echo $this->uploadPathNameSource;?> = '<?php echo $this->uploadPathDirectorySource;?>/'.$this-><?php echo $primaryKeyColumn; ?>;
<?php } else {?>
		$<?php echo $this->uploadPathNameSource;?> = '<?php echo $this->uploadPathDirectorySource;?>';
<?php }
	foreach($columns as $name=>$column) {
		if($column->dbType == 'text' && $column->comment == 'file') {?>

		if($this-><?php echo $name;?> != '' && file_exists($<?php echo $this->uploadPathNameSource;?>.'/'.$this-><?php echo $name;?>))
			rename($<?php echo $this->uploadPathNameSource;?>.'/'.$this-><?php echo $name;?>, '<?php echo $this->uploadPathDirectorySource;?>/verwijderen/'.$this-><?php echo $primaryKeyColumn; ?>.'_'.$this-><?php echo $name;?>);
<?php 	}
	}
}?>
		// Create action
	}
<?php }?>
}