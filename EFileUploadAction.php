<?php
/**
 * EFileUploadAction class file.
 *
 * @author Veaceslav Medvedev <slavcopost@gmail.com>
 * @link http://code.google.com/p/yiiext/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
/**
 * EFileUploadAction file upload action.
 *
 * @author Veaceslav Medvedev <slavcopost@gmail.com>
 * @version 0.1
 * @package yiiext.actions.fileUpload
 */
class EFileUploadAction extends CAction
{
	/**
	 * @var CModel|string|null the data model which contains file attribute with validation rules.
	 */
	public $model;
	/**
	 * @var string the model attribute.
	 */
	public $attribute;
	/**
	 * @var string the input field name. This must be resolve from {@link model} and {@link attribute}.
	 * This property is required if not set {@link model}.
	 */
	public $name;
	/**
	 * @var string the directory where save files. Path must be relative from webroot.
	 * Defaults to ''.
	 */
	public $path='';
	/**
	 * @var boolean try create directory if not exists. Defaults to false.
	 * @see http://php.net/manual/en/function.mkdir.php
	 */
	public $createDirectory=false;
	/**
	 * @var int which means the widest possible access. Defaults to 0644.
	 * @see http://php.net/manual/en/function.mkdir.php
	 */
	public $createDirectoryMode=0644;
	/**
	 * @var int whether create directories recursive. Defaults to false.
	 * @see http://php.net/manual/en/function.mkdir.php
	 */
	public $createDirectoryRecursive=false;
	/**
	 * @var string the rule for generate filename.
	 */
	public $filenameRule;
	/**
	 * @var string the filename. If not set will be copied original filename.
	 * @link CUploadedFile::getName()
	 */
	public $filename;
	/**
	 * @var boolean whether terminate application after file will save. It often need for ajax request.
	 * Defaults to false.
	 */
	public $exitOnAjax=false;
	/**
	 * @var CUploadedFile|null the uploaded file.
	 */
	protected $_file;

	/**
	 * @return CUploadedFile|null the uploaded file.
	 */
	public function getFile()
	{
		return $this->_file;
	}
	/**
	 * @throws CException
	 * @return void
	 */
	public function run()
	{
		// Check Path
		$this->path='/'.trim($this->path,'/');
		$path=Yii::getPathOfAlias('webroot').$this->path;

		if(!is_dir($path))
		{
			if($this->createDirectory===true)
			{
				if(!mkdir($path,$this->createDirectoryMode,$this->createDirectoryRecursive))
					throw new CException(Yii::t('yiiext','Cannot create directory {dir}.',array('{dir}'=>$this->path)));
			}
			else
				throw new CException(Yii::t('yiiext','Invalid path {path}.',array('{path}'=>$this->path)));
		}
		
		// If model set, check attribute and generate input filed name.
		if(is_string($this->model))
			$this->model=new $this->model;
		if($this->hasModel())
			$this->name=CHtml::activeName($this->model,$this->attribute);

		// Check input field name.
		if(empty($this->name))
			throw new CException(Yii::t('yiiext','Input field name required.'));

		// Check file exists.
		if(($this->_file=CUploadedFile::getInstanceByName($this->name))!==null)
		{
			// If model set, validate it.
			if($this->model instanceof CModel)
			{
				$this->model->{$this->attribute}=$this->_file;
				if(!$this->model->validate())
					throw new CException(Yii::t('yiiext',$this->model->getError($this->attribute)));
			}

			// If set filename rule, evaluate it.
			if($this->filenameRule!==null)
				$this->filename=$this->evaluateExpression($this->filenameRule,array('file'=>$this->_file));

			// If filename not set, get the original uploaded filename.
			if($this->filename===null)
				$this->filename=$this->_file->getName();

			// Run beforeSave events, and save file.
			if($this->beforeSave())
			{
				// Save file.
				if(!$this->_file->saveAs($path.'/'.$this->filename))
					throw new CException(Yii::t('yiiext','Cannot save file.'));

				// Run afterSave events.
				$this->afterSave();
				Yii::trace(Yii::t('yiiext',"File success saved."));

				if($this->exitOnAjax && Yii::app()->getRequest()->getIsAjaxRequest())
					exit;
			}
		}
		else
			throw new CException(Yii::t('yiiext','File not sent.'));
	}
	/**
	 * @return boolean whether this action is associated with a data model.
	 */
	protected function hasModel()
	{
		return $this->model instanceof CModel && $this->attribute!==null;
	}
	/**
	 * @param CEvent $event
	 * @return boolean
	 */
	public function onBeforeSave($event)
	{
		return $this->raiseEvent('onBeforeSave',$event);
	}
	/**
	 * @param CEvent $event
	 * @return void
	 */
	public function onAfterSave($event)
	{
		$this->raiseEvent('onAfterSave',$event);
	}
	/**
	 * @return boolean
	 */
	protected function beforeSave()
	{
		if($this->hasEventHandler('onBeforeSave'))
			return $this->onBeforeSave(new CEvent($this));
		return true;
	}
	/**
	 * @return void
	 */
	protected function afterSave()
	{
		if($this->hasEventHandler('onAfterSave'))
			$this->onAfterSave(new CEvent($this));
	}
}
