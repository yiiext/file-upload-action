File upload action
==================

## Usage

```php
// add action to controller
public function actions()
{
	return array(
		// ...
		'upload'=>array(
			'class' => 'ext.yiiext.actions.fileUpload.EFileUploadAction',
			// The data model which contains file attribute with validation rules.
			'model' => null,
			// The model attribute.
			'attribute' => null,
			// The input field name. This must be resolve from model and attribute.
			'name' => 'upload-file',
			// Try create directory if not exists. Defaults to false.
			'createDirectory' => false,
			// Which means the widest possible access.
			'createDirectoryMode' => 0644,
			// Which create directories recursive.
			'createDirectoryRecursive' => false,
			// The rule for generate filename.
			// i.e. 'filenameRule' => 'md5($file->name) . "." . $file->extensionName',
			'filenameRule' => null,
			// The filename. If not set will be copied original filename.
			'filename' => null,
			// The directory where save files.
			'path' => null,
			'onBeforeUpload' => function ($event) {
				// Change default path via event.
				$event->sender->path=Yii::getPathOfAlias('webroot') . '/files';
			},
			'onBeforeSave' => function ($event) {
				// Add error and cancel uploading.
				$event->sender->addError(Yii::t('yiiext', 'Process stopped!'));
				$event->isValid = false;
			},
			'onAfterSave' => function($event) {
				// i.e. make thumb for uploaded image.
			}
			'onAfterUpload' => function ($event) {
				if($event->sender->hasErrors()) {
					// Show error if exists.
					echo implode(', ', $event->sender->getErrors());
				} else {
					// Return url.
					echo str_replace(Yii::getPathOfAlias('webroot'), '', $event->sender->path) . '/' . $event->sender->filename;
				}
				// Stop application.
				exit;
			}
		),
		// ...
	);
}
```

## Events

Action has 4 events and runs in the following order:

* **onBeforeUpload** - this event is mainly intended to change the default settings for the action: save path, file name, etc.
* **onBeforeSave** - intended for a more detailed validation of the downloaded file, what can be done by means of the model.
  This event can be canceled to save the file by setting the `$event->isValid = false`
* **onAfterSave** - an event designed to manipulate already saved file.
* **onAfterUpload** - in this event are invited to check the load, save or no. And show the error.
