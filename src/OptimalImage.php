<?php

namespace Yarbala\OptimalImage;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Laravel\Nova\Fields\Image;
use Spatie\ImageOptimizer\OptimizerChain as ImageOptimizer;
use Illuminate\Support\Facades\Storage;

/**
 * Данный класс унаследован от станадартного класса Image. Его предназаначение, это переопределить процесс записи
 * изображения таким образом, чтобы обработать изображение после его загрузки.
 *
 * Class OptimalImage
 * @package Yarbala\OptimalImage
 */
class OptimalImage extends Image
{
    /**
     * Функция переопределяет базовую из родительского классса для Image 'Fields/File.php'. Код взят из базовой функции
     * и ы него добавлен вызов optimizeImage после загрузки.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $requestAttribute
     * @return string
     */
    protected function storeFile($request, $requestAttribute)
    {
        if (! $this->storeAsCallback) {
            $fileName = $request->file($requestAttribute)->store($this->getStorageDir(), $this->getStorageDisk());
            if ($fileName) {
                try {
                    $this->optimizeImage($fileName);
                } catch (FileNotFoundException $e) {
                }
            }

            return $fileName;
        }

        $fileName = $request->file($requestAttribute)->storeAs(
            $this->getStorageDir(), call_user_func($this->storeAsCallback, $request), $this->getStorageDisk()
        );

        if ($fileName) {
            try {
                $this->optimizeImage($fileName);
            } catch (FileNotFoundException $e) {
                //
            }
        }

        return $fileName;
    }

    /**
     * Функция производящая оптимизацию изображения
     *
     * @param $fileName
     * @throws FileNotFoundException
     */
    protected function optimizeImage($fileName)
    {
        $needsUploadBack = false;
        $localDisk = $this->getLocalDisk();
        $disk = $this->getStorageDisk();

        /*  Так как базовый компонент работает с классом Storage, а Spatie\ImageOptimizer работает с локальными файлами
            мы выгрузим файл в локальное хранилище в случае надобности
        */
        if (!Storage::disk($localDisk)->exists($fileName)) {
            Storage::disk($localDisk)->put($fileName, Storage::disk($disk)->get($fileName));
            $needsUploadBack = true;
        }

        // Получаем путь к изображению в локальном хранилище
        $path = Storage::disk($localDisk)->path($fileName);

        // Оптимизация изображения
        app(ImageOptimizer::class)->optimize($path);

        // Если мы вгружали файл, загрузим его обратно
        if ($needsUploadBack) {
            Storage::disk($disk)->put($fileName, Storage::disk($localDisk)->get($fileName));
            Storage::disk($localDisk)->delete($fileName);
        }
    }

    /**
     * Установка локального хранилища для обработки изображений
     *
     * @param string $disk
     * @return OptimalImage
     */
    public function localDisk(string $disk)
    {
        return $this->withMeta(['localDisk' => $disk]);
    }

    /**
     * Получение локального хранилища
     *
     * @return string
     */
    protected function getLocalDisk()
    {
        return $this->meta['localDisk'] ?? 'local';
    }
}
