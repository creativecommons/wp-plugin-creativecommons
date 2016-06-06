<?php
/*
   Plugin Name: Creative Commons
   Description: Official Creative Commons plugin for WordPress. Allows
   users to select and display Creative Commons licenses for their
   content. Partially inspired by the License plugin by mitcho (Michael
   Yoshitaka Erlewine) and Brett Mellor, as well as the original
   WpLicense plugin by CC CTO Nathan R. Yergler.
   Version: 2.0
   Author: Matt Lee <mattl@creativecommons.org>, Bjorn Wijers <burobjorn@burobjorn.nl>, Tarmo Toikkanen <tarmo@iki.fi>
   Plugin URI: http://wiki.creativecommons.org/WpLicense
   License: GPLv2 or later versions
 */

add_filter("attachment_fields_to_edit", "add_image_source_url", 10, 2);
function add_image_source_url($form_fields, $post) {
    $form_fields["source_url"] = array(
	"label" => __("Source URL"),
	"input" => "text",
	"value" => get_post_meta($post->ID, "source_url", true),
	"helps" => __("Add the URL where the original image was posted"),
    );

    $form_fields["license_url"] = array(
	"label" => __("License URL"),
	"input" => "text",
	"value" => get_post_meta($post->ID, "license_url", true),
	"helps" => __("Add the URL for the license for the work"),
    );

    return $form_fields;
}

add_filter("attachment_fields_to_save", "save_image_source_url", 10 , 2);
function save_image_source_url($post, $attachment) {
    if (isset($attachment['source_url']))
	update_post_meta($post['ID'], 'source_url', esc_url($attachment['source_url']));
    if (isset($attachment['license_url']))
	update_post_meta($post['ID'], 'license_url', esc_url($attachment['license_url']));
    return $post;
}

add_filter( 'img_caption_shortcode', 'cc_caption_image', 10, 3 );

function cc_caption_image($empty, $attr, $content) {
    extract(shortcode_atts(array(
	'id'=> '',
	'align'=> 'alignnone',
	'width'=> '',
	'caption' => '',
	'title' => '',
    ), $attr));


    // Extract attachment $post->ID
    preg_match('/\d+/', $attr['id'], $att_id);

    $license_url = get_post_meta($att_id[0], 'license_url', true);

    $license_url = strtolower($license_url);
    
    if (strpos($license_url, "creativecommons")) {
	if (substr($license_url,-1) != "/") {
	    $license_url = $license_url . "/";
	}
    }

    $meta = wp_get_attachment_metadata($att_id[0]);

    //print_r($meta);
    
    $title =  trim($meta['image_meta']['title']);
    $credit =  trim($meta['image_meta']['credit']);

    error_log(strlen($title));
    
    $original = $caption;

    $opener = '<div class="cc-copyright wp-caption-text" style="background: yellow; border: 1px solid red; padding: 1em;">';

    $caption .= $opener;

    if (is_numeric($att_id[0]) && $source_url = get_post_meta($att_id[0], 'source_url', true)) {
	$parts = parse_url($source_url);

	if ($title) {
	    if ($credit) {	
		$caption .= ' '. __('via') .' <a href="'. $source_url .'">'. $title .'</a> by ' . $credit . '';
	    }
	    else {
		$caption .= ' '. __('via') .' <a href="'. $source_url .'">'. $title .'</a>';
	    }
	}
	else {
	    $caption .= ' '. __('via') .' <a href="'. $source_url .'">'. $parts['host'] .'</a>';
	}
	
    }
    else {
    if ($title) {
    if ($credit) {

    $caption .= ' ( ' . $title .' by ' . $credit . ')';

    }
    else {

    $caption .= ' ( ' . $title . ')';
	
    }
    }
    }
    

    
    

    if (! $license_url) {
	$copyright =  strtolower($meta['image_meta']['copyright']);

	if (strpos($copyright, "creative commons attribution 2.0"))                          { $license_url = "http://creativecommons.org/licenses/by/2.0/"; }
	if (strpos($copyright, "creative commons attribution-sharealike 2.0"))               { $license_url = "http://creativecommons.org/licenses/by-sa/2.0/"; }
	if (strpos($copyright, "creative commons attribution-sharealike-noncommercial 2.0")) { $license_url = "http://creativecommons.org/licenses/by-sa-nc/2.0/"; }
	if (strpos($copyright, "creative commons attribution-noderivs 2.0"))                 { $license_url = "http://creativecommons.org/licenses/by-nd/2.0/"; }
	if (strpos($copyright, "creative commons attribution-noncommercial-noderivs 4.0"))   { $license_url = "http://creativecommons.org/licenses/by-nc-nd/2.0/"; }
	if (strpos($copyright, "creative commons attribution-noncommercial-sharealike 4.0")) { $license_url = "http://creativecommons.org/licenses/by-nc-sa/2.0/"; }

	if (strpos($copyright, "creative commons attribution 3.0"))                          { $license_url = "http://creativecommons.org/licenses/by/3.0/"; }
	if (strpos($copyright, "creative commons attribution-sharealike 3.0"))               { $license_url = "http://creativecommons.org/licenses/by-sa/3.0/"; }
	if (strpos($copyright, "creative commons attribution-sharealike-noncommercial 3.0")) { $license_url = "http://creativecommons.org/licenses/by-sa-nc/3.0/"; }
	if (strpos($copyright, "creative commons attribution-noderivatives 3.0"))            { $license_url = "http://creativecommons.org/licenses/by-nd/3.0/"; }
	if (strpos($copyright, "creative commons attribution-noncommercial-noderivatives 4.0"))   { $license_url = "http://creativecommons.org/licenses/by-nc-nd/3.0/"; }
	if (strpos($copyright, "creative commons attribution-noncommercial-sharealike 4.0")) { $license_url = "http://creativecommons.org/licenses/by-nc-sa/3.0/"; }

	if (strpos($copyright, "creative commons attribution 4.0"))                          { $license_url = "http://creativecommons.org/licenses/by/4.0/"; }
	if (strpos($copyright, "creative commons attribution-sharealike 4.0"))               { $license_url = "http://creativecommons.org/licenses/by-sa/4.0/"; }
	if (strpos($copyright, "creative commons attribution-sharealike-noncommercial 4.0")) { $license_url = "http://creativecommons.org/licenses/by-sa-nc/4.0/"; }
	if (strpos($copyright, "creative commons attribution-noderivatives 4.0"))            { $license_url = "http://creativecommons.org/licenses/by-nd/4.0/"; }
	if (strpos($copyright, "creative commons attribution-noncommercial-noderivatives 4.0"))   { $license_url = "http://creativecommons.org/licenses/by-nc-nd/4.0/"; }
	if (strpos($copyright, "creative commons attribution-noncommercial-sharealike 4.0")) { $license_url = "http://creativecommons.org/licenses/by-nc-sa/4.0/"; }

    }

    if ($license_url) {
   
    if (strpos($license_url, "/by/")) { $license_code = "CC BY"; $button = "by"; }
    if (strpos($license_url, "/by-sa/")) { $license_code = "CC BY-SA"; $button = "by-sa"; }
    if (strpos($license_url, "/by-nc-sa/")) { $license_code = "CC BY-NC-SA"; $button = "by-nc-sa"; }
    if (strpos($license_url, "/by-nd/")) { $license_code = "CC BY-ND"; $button = "by-nd"; }
    if (strpos($license_url, "/by-nc/")) { $license_code = "CC NC"; $button = "by-nc"; }
    if (strpos($license_url, "/by-nc-nd/")) { $license_code = "CC NC-ND"; $button = "by-nc-nd"; }
    if (strpos($license_url, "/publicdomain/")) { $license_code = "public domain"; $button = "pd"; }
     
    $caption .= ' <a rel="license" href="' . $license_url . '">' . $license_code . '</a>';

    if ($button != "pd" && $button) {
    $caption .= '<a rel="license" href="' . $license_url . '"><img src="https://licensebuttons.net/l/' . $button . '/4.0/88x31.png" alt="" width="76" height="22" /></a><br />';
    }

    }

    // RDF stuff

    if (! $title) {
	$title = $original;
    }

    if ($caption != $original . $opener) {

    $caption .= '<!-- RDFa! --><span xmlns:dct="http://purl.org/dc/terms/" property="dct:title">' . $title . '</span>';

    if ($credit) {
	if ($source_url) {
	    $caption .= ' by <a xmlns:cc="http://creativecommons.org/ns#" href="' . $source_url . '" property="cc:attributionName" rel="cc:attributionURL">' . $credit . '</a> is licensed under the <a rel="license" href="' . $license_url . '">' . $license_url . '</a>';
	}
	else {
	    $caption .= ' by '. $credit . ' is licensed under the <a rel="license" href="' . $license_url . '">' . $license_url . '</a>';
	}
    }

    $caption .= "<!-- end of RDFa! -->";

    } else {

    $caption .= "<p>Sorry for the empty ugly box. We'll remove that before release. It also means we don't have even a CC license for this image. Go set one in the Media Library.</p>";
    
    }

    $caption .= '</div>';
    
    if (1 > (int) $width || empty($caption))
	return $caption;

    if ($id)
	$id = 'id="' . esc_attr($id) . '" ';

    return '<div ' . $id . 'class="cc-caption wp-caption ' . esc_attr($align) . '" style="width: ' . (10 + (int) $width) . 'px">'
	 . do_shortcode($content) . '<p class="wp-caption-text">' . $caption . '</p></div>';
}





?>
