Действие для загрузки файла
===========================

## Использование

```php
// Добавляем действие в контроллер
public function actions()
{
	return array(
		// ...
		'upload' = >array(
			'class' => 'ext.yiiext.actions.fileUpload.EFileUploadAction',
			// Модель в которой есть файл-атрибут с валидацией.
			// Если указана модель и атрибут, будет сгенерировано имя input-поля.
			'model' => null,
			// Атрибут в который загружен файл.
			'attribute' => null,
			// Имя input-поля на странице в который будет загружен файл.
			'name' => 'upload-file',
			// Указывает будет ли скрипт пытаться создать папку загрузки, при ее отсутствии.
			'createDirectory' => false,
			// Права доступа к создаваемой папке.
			'createDirectoryMode' => 0644,
			// Пытаться ли создать папку рекурсивно.
			'createDirectoryRecursive' => false,
			// Правило для генерации имени файла. Это php-выражение где $file это загруженный файл.
			// Например 'filenameRule' => 'md5($file->name) . "." . $file->extensionName',
			'filenameRule' => null,
			// Имя файла, если не указывать, будет использовано оригинальное.
			'filename' => null,
			// Путь сохранения файла.
			'path' => null,
			'onBeforeUpload' => function ($event) {
				// Меняем путь установленный по умолчанию.
				$event->sender->path = Yii::getPathOfAlias('webroot') . '/files';
			},
			'onBeforeSave' => function ($event) {
				// Можем добавить ошибку и отменить сохранение.
				$event->sender->addError(Yii::t('yiiext', 'Сохранение отменено!'));
				$event->isValid = false;
			},
			'onAfterSave' => function ($event) {
				// Например, создаем превьюшку для картинки.
			}
			'onAfterUpload' => function ($event) {
				if ($event->sender->hasErrors()) {
					// Если есть ошибки покажим их.
					echo implode(', ', $event->sender->getErrors());
				} else {
					// Вернем ссылку на файл.
					echo str_replace(Yii::getPathOfAlias('webroot'), '', $event->sender->path) . '/' . $event->sender->filename;
				}
				// Остановим приложение для ajax'а.
				exit;
			}
		),
		// ...
	);
}
```

### События

Действие имеет 4 события и выполняются в следующем порядке:

- **onBeforeUpload** - это событие, в основном предназначено для изменения стандартных настроек действия: путь сохранения, имя файла и др.
- **onBeforeSave** - предназначено для более детальной валидации загруженного файла, то что нельзя сделать средствами модели.
  В этом событии можно отменить сохранение файла, установив $event->isValid = false
- **onAfterSave** - событие предназначенное для манипуляции с уже сохраненым файлом.
- **onAfterUpload** - в этом событии предлагается проверять загрузился, сохранился или нет. И показать ошибки.
