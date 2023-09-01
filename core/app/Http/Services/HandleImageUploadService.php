<?php

namespace App\Http\Services;

use App\Models\MediaUploader;
use Intervention\Image\Facades\Image;

class HandleImageUploadService
{
    public static function handle_image_upload(
        $image_db,
        $image,
        $image_name_with_ext,
        $folder_path,
        $request,
        $woocommerce_import = false,
        $woocommerce_import_path = ''
    )
    {
        $image_dimension = getimagesize($image);
        $image_width = $image_dimension[0];
        $image_height = $image_dimension[1];
        $image_dimension_for_db = $image_width . ' x ' . $image_height . ' pixels';
        $image_size_for_db = $image->getSize();

        $image_grid = 'grid-'.$image_db ;
        $image_large = 'large-'. $image_db;
        $image_thumb = 'thumb-'. $image_db;
        $image_tiny = 'tiny-'. $image_db;

        $resize_grid_image = Image::make($image)->resize(350, null,function ($constraint) {
            $constraint->aspectRatio();
        });
        $resize_large_image = Image::make($image)->resize(740, null,function ($constraint) {
            $constraint->aspectRatio();
        });
        $resize_thumb_image = Image::make($image)->resize(150, 150);
        $resize_tiny_image = Image::make($image)->resize(15, 15)->blur(50);

        if (!$woocommerce_import)
        {
            $request->file->move($folder_path, $image_db);
        } else {
            if (!empty($woocommerce_import_path))
            {
                \File::move($woocommerce_import_path.$image_db, $folder_path.$image_db);
            }
        }

        $imageData = [
            'title' => $image_name_with_ext,
            'size' => formatBytes($image_size_for_db),
            'path' => $image_db,
            'user_type' => 0, //0 == admin 1 == user
            'user_id' => \Auth::guard('admin')->id(),
            'dimensions' => $image_dimension_for_db
        ];
        if ($request->user_type === 'user'){
            $imageData['user_type'] = 1;
            $imageData['user_id'] = \Auth::guard('web')->id();
        }
        else if ($request->user_type === 'api'){
            $imageData['user_type'] = 1;
            $imageData['user_id'] = \Auth::guard('sanctum')->id();
        }

        $image_data = MediaUploader::create($imageData);

        if ($image_width > 150){
            $resize_thumb_image->save($folder_path .'thumb/'. $image_thumb);
            $resize_grid_image->save($folder_path .'grid/'. $image_grid);
            $resize_large_image->save($folder_path .'large/'. $image_large);

            $tiny_path = $folder_path .'tiny';
            if (!is_dir($tiny_path))
            {
                mkdir($tiny_path, 0777);
            }
            $resize_tiny_image->save($folder_path .'tiny/'. $image_tiny);
        }

        return $image_data->id ?? '';
    }
}
