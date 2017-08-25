<?php
class WaterMark
{
    /**
     * 图片加水印
     * @param $source 图片资源
     * @param string $target 添加水印后的名字
     * @param string $w_pos 水印位置安排（1-10）【1:左头顶；2:中间头顶；3:右头顶...值空:随机位置】
     * @param string $w_img 水印图片路径
     * @param string $w_text 显示的文字
     * @param int $w_font 字体大小
     * @param string $w_color 字体颜色
     * @return bool|string
     */
    public function work($source, $target = '', $w_pos = '', $w_img = '', $w_text = 'www.aiyu.com',$w_font = 10, $w_color = '#CC0000')
    {
        $this->w_img = './fn/watermark.png';//水印图片
        $this->w_pos = 9;
        $this->w_minwidth = 400;//最少宽度
        $this->w_minheight = 200;//最少高度
        $this->w_quality = 80;//图像质量
        $this->w_pct = 85;//透明度

        $w_pos = $w_pos ? $w_pos : $this->w_pos;
        $w_img = $w_img ? $w_img : $this->w_img;
        if(!$this->check($source)) return false;
        if(!$target) $target = $source;
        $source_info = getimagesize($source);//图片信息
        $source_w  = $source_info[0];//图片宽度
        $source_h  = $source_info[1];//图片高度
        if($source_w < $this->w_minwidth || $source_h < $this->w_minheight) return false;
        switch($source_info[2]) { //图片类型
            case 1 : //GIF格式
                $source_img = imagecreatefromgif($source);
                break;
            case 2 : //JPG格式
                $source_img = imagecreatefromjpeg($source);
                break;
            case 3 : //PNG格式
                $source_img = imagecreatefrompng($source);
                //imagealphablending($source_img,false); //关闭混色模式
                imagesavealpha($source_img,true); //设置标记以在保存 PNG 图像时保存完整的 alpha 通道信息（与单一透明色相反）
                break;
            default :
                return false;
        }
        if(!empty($w_img) && file_exists($w_img)) { //水印图片有效
            $IfWaterImage = 1; //标记
            $water_info  = getimagesize($w_img);
            $width    = $water_info[0];
            $height    = $water_info[1];
            switch($water_info[2]) {
                case 1 :
                    $water_img = imagecreatefromgif($w_img);
                    break;
                case 2 :
                    $water_img = imagecreatefromjpeg($w_img);
                    break;
                case 3 :
                    $water_img = imagecreatefrompng($w_img);
                    imagealphablending($water_img,false);
                    imagesavealpha($water_img,true);
                    break;
                default :
                    return '';
            }
        }else{
            $IfWaterImage = 0;
            // imagettfbbox返回一个含有 8 个单元的数组表示了文本外框的四个角
            $temp = imagettfbbox(ceil($w_font * 3), 0, './fn/texb.ttf', $w_text);
            $width = $temp[2] - $temp[6];
            $height = $temp[3] - $temp[7];
            unset($temp);
        }

        switch($w_pos) {
            case 1:
                $wx = 5;
                $wy = 5;
                break;
            case 2:
                $wx = ($source_w - $width) / 2;
                $wy = 0;
                break;
            case 3:
                $wx = $source_w - $width;
                $wy = 0;
                break;
            case 4:
                $wx = 0;
                $wy = ($source_h - $height) / 2;
                break;
            case 5:
                $wx = ($source_w - $width) / 2;
                $wy = ($source_h - $height) / 2;
                break;
            case 6:
                $wx = $source_w - $width;
                $wy = ($source_h - $height) / 2;
                break;
            case 7:
                $wx = 0;
                $wy = $source_h - $height;
                break;
            case 8:
                $wx = ($source_w - $width) / 2;
                $wy = $source_h - $height;
                break;
            case 9:
                $wx = $source_w - ($width+5);
                $wy = $source_h - ($height+5);
                break;
            case 10:
                $wx = rand(0,($source_w - $width));
                $wy = rand(0,($source_h - $height));
                break;
            default:
                $wx = rand(0,($source_w - $width));
                $wy = rand(0,($source_h - $height));
                break;
        }

        if($IfWaterImage) {
            if($water_info[2] == 3) {
                imagecopy($source_img, $water_img, $wx, $wy, 0, 0, $width, $height);
            }else{
                imagecopymerge($source_img, $water_img, $wx, $wy, 0, 0, $width, $height, $this->w_pct);
            }
        }else{
            if(!empty($w_color) && (strlen($w_color)==7)) {
                $r = hexdec(substr($w_color,1,2));
                $g = hexdec(substr($w_color,3,2));
                $b = hexdec(substr($w_color,5));
            }else{
                return '';
            }
            imagestring($source_img,$w_font,$wx,$wy,$w_text,imagecolorallocate($source_img,$r,$g,$b));
        }

        switch($source_info[2]) {
            case 1 :
                imagegif($source_img, $target);
//GIF 格式将图像输出到浏览器或文件(欲输出的图像资源, 指定输出图像的文件名)
                break;
            case 2 :
                imagejpeg($source_img, $target, $this->w_quality);
                break;
            case 3 :
                imagepng($source_img, $target);
                break;
            default :
                return '图片格式有误';
        }

        if(isset($water_info)){
            unset($water_info);
        }
        if(isset($water_img)) {
            imagedestroy($water_img);
        }
        unset($source_info);
        imagedestroy($source_img);
        return true;
    }

    public function check($image){
        return extension_loaded('gd') && preg_match("/\.(jpg|jpeg|gif|png)/i", $image, $m) && file_exists($image) && function_exists('imagecreatefrom'.($m[1] == 'jpg' ? 'jpeg' : $m[1]));
    }
}
