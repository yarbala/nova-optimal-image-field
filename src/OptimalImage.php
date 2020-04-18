<?php

namespace Yarbala\OptimalImage;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Laravel\Nova\Fields\Image;
use Spatie\ImageOptimizer\OptimizerChain as ImageOptimizer;
use Storage;

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
     * Функция производящая оптимизацию изображение
     *
     * @param $fileName
     * @throws FileNotFoundException
     */
    protected function optimizeImage($fileName)
    {
        $needsUploadBack = false;
        $localDisk = 'local';
        $disk = $this->getStorageDisk();

        /*  Так как базовый компонент работает с классом Storage, а Spatie\ImageOptimizer работает с локальными файлами
            мы выгрузим файл в локальное хранилище в случае надобности
        */
        if ($disk === $localDisk || Storage::disk($localDisk)->exists($fileName)) {
            $path = Storage::disk($localDisk)->path($fileName);
        } else {
            Storage::disk($localDisk)->put($fileName, Storage::disk($disk)->get($fileName));
            $path = Storage::disk($localDisk)->path($fileName);
            $needsUploadBack = true;
        }

        // Оптимизация изображения
        app(ImageOptimizer::class)->optimize($path);

        // Если мы вгружали файл, загрузим его обратно
        if ($needsUploadBack) {
            Storage::disk($disk)->put($fileName, Storage::disk($localDisk)->get($fileName));
        }
    }
}
