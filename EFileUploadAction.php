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
 * @version 0.3
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
	 * @var string the directory where save files.
	 * Defaults to webroot directory.
	 */
	public $path;
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
	 * @var string the filename.
	 * Defaults to original filename.
	 * @link CUploadedFile::getName()
	 */
	public $filename;
	/**
	 * @var string the rule for generate filename.
	 */
	public $filenameRule;
	/**
	 * @var CUploadedFile|null the uploaded file.
	 */
	protected $_file;
	protected $_errors=array();

	/**
	 * @return CUploadedFile|null the uploaded file.
	 */
	public function getFile()
	{
		return $this->_file;
	}
	/**
	 * Returns a value indicating whether there is any error.
	 * @return boolean whether there is any error.
	 */
	public function hasErrors()
	{
		return $this->_errors!==array();
	}
	/**
	 * Returns the process errors.
	 * @return array errors. Empty array is returned if no error.
	 */
	public function getErrors()
	{
		return $this->_errors;
	}
	/**
	 * Adds a new error.
	 * @param string $error new error message.
	 */
	public function addError($error)
	{
		$this->_errors[]=strval($error);
	}
	public function run()
	{
		// If model set, check attribute and generate input filed name.
		if(is_string($this->model))
		{
			$this->model=new $this->model;
		}
		if($this->hasModel())
		{
			$this->name=CHtml::activeName($this->model,$this->attribute);
		}
		// Check input field name.
		if(empty($this->name))
		{
			throw new CException(Yii::t('yiiext','Input field name required.'));
		}
		// Upload files.
		$this->upload();
		// Show errors.
		if($this->hasErrors())
			throw new CException(implode("\n",$this->getErrors()));
	}
	/**
	 * @throws CException
	 * @return void
	 */
	protected function upload()
	{
		$this->beforeUpload();
		// Check file exists.
		if(($this->_file=CUploadedFile::getInstanceByName($this->name))!==null)
		{
			// If model set, validate it.
			if($this->hasModel())
			{
				$this->model->{$this->attribute}=$this->_file;
				if(!$this->model->validate())
				{
					array_walk_recursive($this->model->getErrors(), array($this,'addError'));
					return;
				}
			}

			// Prepare directory.
			if($this->path===null)
			{
				$this->path=Yii::getPathOfAlias('webroot');
			}
			else if(!is_dir($this->path))
			{
				if($this->createDirectory===true)
				{
					if(!mkdir($this->path,$this->createDirectoryMode,$this->createDirectoryRecursive))
					{
						$this->addError(Yii::t('yiiext','Cannot create directory "{dir}".',array('{dir}'=>$this->path)));
						return;
					}
					Yii::trace('Create directory "{dir}"',array('{dir}'=>$this->path));
				}
				else
				{
					$this->addError(Yii::t('yiiext','Invalid path "{path}".',array('{path}'=>$this->path)));
					return;
				}
			}

			if($this->filename===null)
			{
				// If set filename rule, evaluate it.
				if($this->filenameRule!==null)
					$this->filename=$this->evaluateExpression($this->filenameRule,array('file'=>$this->getFile()));
				// If filename not set, get the original uploaded filename.
				if($this->filename===null)
					$this->filename=$this->getFile()->getName();
			}

			// Run beforeSave events.
			if($this->beforeSave())
			{
				// Save file.
				$filepath=rtrim($this->path,'/').'/'.$this->filename;
				if(!$this->_file->saveAs($filepath))
				{
					$this->addError(Yii::t('yiiext','Cannot save file "{filepath}".',array('{filepath}'=>$filepath)));
					return;
				}
				Yii::trace(Yii::t('yiiext','File "{filepath}" success saved.',array('{filepath}'=>$filepath)));
				// Run afterSave events.
				$this->afterSave();
			}
			$this->afterUpload();
		}
		else
		{
			$this->addError(Yii::t('yiiext','File not sent.'));
		}
	}
	/**
	 * @return boolean whether this action is associated with a data model.
	 */
	protected function hasModel()
	{
		return $this->model instanceof CModel&&$this->attribute!==null;
	}
	/**
	 * @param CEvent $event
	 * @return void
	 */
	public function onBeforeSave($event)
	{
		$this->raiseEvent('onBeforeSave',$event);
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
	 * @param CEvent $event
	 * @return void
	 */
	public function onBeforeUpload($event)
	{
		$this->raiseEvent('onBeforeUpload',$event);
	}
	/**
	 * @param CEvent $event
	 * @return void
	 */
	public function onAfterUpload($event)
	{
		$this->raiseEvent('onAfterUpload',$event);
	}
	/**
	 * @return boolean
	 */
	protected function beforeSave()
	{
		if($this->hasEventHandler('onBeforeSave'))
		{
			$event=new CModelEvent($this);
			$this->onBeforeSave($event);
			return $event->isValid;
		}
		else
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
	/**
	 * @return void
	 */
	protected function beforeUpload()
	{
		if($this->hasEventHandler('onBeforeUpload'))
			$this->onBeforeUpload(new CEvent($this));
	}
	/**
	 * @return void
	 */
	protected function afterUpload()
	{
		if($this->hasEventHandler('onAfterUpload'))
			$this->onAfterUpload(new CEvent($this));
	}
}
