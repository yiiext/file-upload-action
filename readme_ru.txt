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
			'onBeforeUpload'=>function($event)
			{
				// меняем путь установленный по умолчанию
				$event->sender->path=Yii::getPathOfAlias('webroot').'/files';
			},
			'onBeforeSave'=>function($event)
			{
				// Можем добавить ошибку и отменить сохранение
				$event->sender->addError(Yii::t('yiiext','Сохранение отменено!'));
				$event->isValid=false;
			},
			'onAfterSave'=>function($event)
			{
				// например, создаем превьюшку для картинки.
			}
			'onAfterUpload'=>function($event)
			{
				if($event->sender->hasErrors())
					// если есть ошибки покажим их
					echo implode(', ',$event->sender->getErrors());
				else
					// вернем ссылку на файл
					echo str_replace(Yii::getPathOfAlias('webroot'),'',$event->sender->path).'/'.$event->sender->filename;

				// остановим приложение для ajax'а
				exit;
			}
		),
		// ...
	);
}
~~~

События
-------------
Действие имеет 4 события и выполняются в следующем порядке: onBeforeUpload, onBeforeSave, onAfterSave, onAfterUpload.
* onBeforeUpload - это событие, в основном предназначено для изменения стандартных настрооек действия: путь сохранения, имя файла и др.
* onBeforeSave - предназначено для более детальной валидации загруженного файла, то что нельзя сделать средствами модели.
  В этом событии можно отменить сохранение файла, установив $event->isValid=false
* onAfterSave - событие предназначенное для манипуляции с уже сохраненым файлом.
* onAfterUpload - в этом событии предлагается проверять загрузился, сохранился и нет. И показать ошибки.