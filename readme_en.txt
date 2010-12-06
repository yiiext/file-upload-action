EFileUploadAction
===============

File upload action.

Usage
-------------
~~~
[php]
// add action to controller
public function actions()
{
	return array(
		// ...
		'upload'=>array(
			'class'=>'ext.yiiext.actions.fileUpload.EFileUploadAction',
			// the data model which contains file attribute with validation rules.
			'model'=null,
			// the model attribute.
			'attribute'=null,
			// the input field name. This must be resolve from model and attribute.
			'name'='upload-file',
			// the directory where save files. Path must be relative from webroot.
			'path'=>'',
			// try create directory if not exists. Defaults to false.
			'createDirectory'=false,
			// which means the widest possible access.
			'createDirectoryMode'=>0644,
			// which create directories recursive.
			'createDirectoryRecursive'=>false,
			// the rule for generate filename.
			// i.e. 'filenameRule'='md5($file->name).".".$file->extensionName',
			'filenameRule'=null,
			// the filename. If not set will be copied original filename.
			'filename'=null,
			// whether terminate application after file will save. It often need for ajax request.
			'exitAfterSave'=>false,
		),
		// ...
	);
}
~~~
