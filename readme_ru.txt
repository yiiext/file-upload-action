EFileUploadAction
===============

Действие для загрузки файла

Использование
-------------
~~~
[php]
// Добавляем действие в контроллер
public function actions()
{
	return array(
		// ...
		'upload'=>array(
			'class'=>'ext.yiiext.actions.fileUpload.EFileUploadAction',
			// модель в которой есть файл-атрибут с валидацией.
			// если указана модель и атрибут, будет сгенерировано имя input-поля
			'model'=null,
			// атрибут в который загружен файл
			'attribute'=null,
			// имя input-поля на странице в который будет загружен файл
			'name'='upload-file',
			// указывает будет ли скрипт пытаться создать папку загрузки, при ее отсутствии
			'createDirectory'=false,
			// права доступа к создаваемой папке
			'createDirectoryMode'=>0644,
			// пытаться ли создать папку рекурсивно
			'createDirectoryRecursive'=>false,
			// правило для генерации имени файла. это php-выражение где $file это загруженный файл
			// например 'filenameRule'='md5($file->name).".".$file->extensionName',
			'filenameRule'=null,
			// Имя файла, если не указывать, будет использовано оригинальное
			'filename'=null,
			// путь сохранения файла, относительно webroot
			'path'=>null,
			// before save событие
			'onBeforeSave'=>function($event)
			{
				// подменяем путь для сохранения
				$event->sender->path=Yii::getPathOfAlias('webroot').'/files';
				// меняем имя файла
				$event->sender->filename='file'.date('YmdHis').'.'.$event->sender->file->getExtensionName();
			},
			// after save событие
			'onAfterSave'=>function($event)
			{
				// вернем ссылку на файл
				echo str_replace(Yii::getPathOfAlias('webroot'),'',$event->sender->path).'/'.$event->sender->filename;
				// останвоим приложение для ajax'а
				exit;
			}
		),
		// ...
	);
}
~~~
