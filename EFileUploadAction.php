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
 * @version 0.2
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
	 * @var CUploadedFile|null the uploaded file.
	 */
	protected $_file;
	/**
	 * @var string the directory where save files.
	 * Defaults to webroot directory.
	 */
	protected $_path;
	/**
	 * @var string the filename.
	 * Defaults to original filename.
	 * @link CUploadedFile::getName()
	 */
	protected $_filename;

	/**
	* The directory where save files. Defaults to webroot directory.
	* @return string the directory path.
	*/
	public function getPath()
	{
		if($this->_path===null)
			return Yii::getPathOfAlias('webroot');
		
		if(!is_dir($this->_path))
		{
			if($this->createDirectory===true)
			{
				if(!mkdir($this->_path,$this->createDirectoryMode,$this->createDirectoryRecursive))
					throw new CException(Yii::t('yiiext','Cannot create directory "{dir}".',array('{dir}'=>$this->_path)));
				Yii::trace('Create directory "{dir}"',array('{dir}'=>$this->_path));
			}
			else
				throw new CException(Yii::t('yiiext','Invalid path "{path}".',array('{path}'=>$this->_path)));
		}
		return $this->_path;
	}
	/**
	* Setup the directory where save files.
	* @param string $path
	*/
	public function setPath($path)
	{
		$this->_path=$path;
	}
	/**
	* The filename. May be fixed name or expression.
	* Defaults to original filename.
	* @return string the filename.
	* @link self::filenameRule
	* @link CUploadedFile::getName()
	*/
	public function getFilename()
	{
		if($this->_filename===null)
		{
			// If set filename rule, evaluate it.
			if($this->filenameRule!==null)
				$this->_filename=$this->evaluateExpression($this->filenameRule,array('file'=>$this->getFile()));
			// If filename not set, get the original uploaded filename.
			if($this->_filename===null)
				$this->_filename=$this->getFile()->getName();
		}
		return $this->_filename;
	}
	/**
	* Setup fixed name to file.
	* @param string $filename
	*/
	public function setFilename($filename)
	{
		$this->_filename=$filename;
	}
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
			if($this->hasModel())
			{
				$this->model->{$this->attribute}=$this->_file;
				if(!$this->model->validate())
					throw new CException(Yii::t('yiiext',$this->model->getError($this->attribute)));
			}

			// Run beforeSave events.
			$this->beforeSave();
			// Save file.
			$filepath=rtrim($this->getPath(),'/').'/'.$this->getFilename();
			if(!$this->_file->saveAs($filepath))
				throw new CException(Yii::t('yiiext','Cannot save file "{filepath}".',array('{filepath}'=>$filepath)));
			Yii::trace(Yii::t('yiiext','File "{filepath}" success saved.',array('{filepath}'=>$filepath)));
			// Run afterSave events.
			$this->afterSave();
		}
		else
			throw new CException(Yii::t('yiiext','File not sent.'));
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
	 * @return boolean
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
	 * @return void
	 */
	protected function beforeSave()
	{
		if($this->hasEventHandler('onBeforeSave'))
			$this->onBeforeSave(new CEvent($this));
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
