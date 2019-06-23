<?php
/**
 * Created by PhpStorm.
 * User: zhhuang
 * Date: 2019/6/22
 * Time: 19:36
 */

namespace App\Service;


class Youtube
{
    /**
     * @param $videoId, 如'v=_7wsatiZ3Gs'
     * @return string
     * 根据YouTube视频id, 获取其视频文件url
     */
    public function getVideoUrl($videoId) {
        if ($videoId) {
            $content = file_get_contents("https://www.youtube.com/get_video_info?video_id=$videoId");

            parse_str($content, $data);
            $streams = $data['url_encoded_fmt_stream_map'];
            $streams = explode(',', $streams);
            $videos = array();
            foreach ($streams as $v) {
                parse_str($v, $data);
                $videos[] = $data;
            }

            $url = $videos[0]['url'];
            return $url;
        }
    }

    /**
     * @param $videoId, 如'v=_7wsatiZ3Gs'
     * @return string
     * 默认封面图 'http://img.youtube.com/vi/$videoId/default.jpg'
     * 高清封面图 'http://img.youtube.com/vi/$videoId/hqdefault.jpg'
     * 中等清晰度封面图 'http://img.youtube.com/vi/$videoId/mqdefault.jpg'
     * 标准清晰度封面图 'http://img.youtube.com/vi/$videoId/sddefault.jpg'
     * 最大清晰度封面图 'http://img.youtube.com/vi/$videoId/maxresdefault.jpg'
     */
    public function getVideoImageUrl($videoId) {
        if ($videoId) {
            return 'http://img.youtube.com/vi/$videoId/sddefault.jpg';
        }
    }

    /**
     * @param $videoUrl
     * @return int
     * Youtube过期时间, 从获取videoUrl开始的6小时后, 如expire=1561299876
     */
    public function getExpiredAt($videoUrl) {
        $output = parse_url($videoUrl);
        parse_str($output['query'], $output);
        return $output['expire'];
    }

    /**
     * @param $srcUrl, 如'https://www.youtube.com/watch?v=_7wsatiZ3Gs'
     * @return string
     */
    private function extractVideoId($srcUrl) {
        $parsed_url = parse_url($srcUrl);
        if(isset($parsed_url["query"])){
            $query_string = $parsed_url["query"];
            parse_str($query_string, $query_arr);
            if(isset($query_arr["v"])){
                return $query_arr["v"];
            }
        }
    }
}