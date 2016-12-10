<?php
/*
  Author: Bjorn Wijers <burobjorn@burobjorn.nl>, Tarmo Toikkanen <tarmo@iki.fi>, Matt Lee <mattl@creativecommons.org>, Rob Myers <rob@creativecommons.org>
  License: GPLv2 or later versions
*/

class CreativeCommonsImage {

    private static $instance = null;


    private function __construct()
    {
    }
    

    public static function get_instance()
    {
 
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
 
        return self::$instance;
 
    }

    // Extract the first license in triangle brackets from the Exif Copyright
    // FIXME: validate in regex, and handle publicdomain

    function exif_copyright_license_url($copyright) 
    {
        $url = '';
        $matched = preg_match(
            '/<(https?:\/\/creativecommons.org\/licenses\/[^>]+)>/',
            $copyright, $matches
        );
        if ($matched) {
            $url = $matches[1];
        }
        return $url;
    }


    // Extract a url from a string of the form
    // "A. N. Other <https://another.com/home/>"

    function exif_url($exif_value) 
    {
        $url = '';
        $matched = preg_match('/<(https?:\/\/[^>]+)>/', $exif_value, $matches);
        if ($matched) {
            $url = trim($matches[1]);
        }
        return $url;
    }


    // Extract the non-url text from a string of the form
    // "A. N. Other <https://another.com/home/>"

    function exif_text($exif_value) 
    {
        return trim(preg_replace('/<https?:\/\/[^>]+>/', '', $exif_value));
    }


    // Convert a license url into the url for the icon for that license

    function license_button_url($license_url) 
    {
        $url = false;
        $matched = preg_match(
            '/\/(licenses|publicdomain)\/([^\/]+)\//',
            $license_url, $matches
        );
        if ($matched) {
            $button = $matches[2];
            if ($matches[1] == 'publicdomain') {
                $button = 'pd';
            }
            $url = 'https://licensebuttons.net/l/' . $button . '/4.0/88x31.png';
        }
        return $url;
    }


    // Generate the canonical English name for the license with the given url

    function license_name($license_url) 
    {
        $name = '';
        if (strpos($license_url, '/publicdomain/')) {
            $name = 'Public Domain';
        } else {
            $name = 'Creative Commons Attribution';
            if (strpos($license_url, '-nc') !== false) {
                $name .= '-NonCommercial';
            }
            if (strpos($license_url, '-nd') !== false) {
                $name .= '-NoDerivatives';
            } elseif (strpos($license_url, '-sa') !== false) {
                $name .= '-ShareAlike';
            }

            if (strpos($license_url, '/2.0/') !== false) {
                $name .= ' 2.0 Generic';
            } elseif (strpos($license_url, '/2.5/') !== false) {
                $name .= ' 2.5 Generic';
            } elseif (strpos($license_url, '/3.0/') !== false) {
                $name .= ' 3.0 Unported';
            } elseif (strpos($license_url, '/4.0/') !== false) {
                $name .= ' 4.0 International';
            }
            $name .= ' License';
        }
        return $name;
    }


    function maybe_apply_attachment_license_url($post_id, $exif) 
    {
        if (isset($exif['COMPUTED']['Copyright'])) {
            $url = $this->exif_copyright_license_url(
                $exif['COMPUTED']['Copyright']
            );
            // Set the metadata, which wasn't already set
            add_post_meta($post_id, 'license_url', $url, true);
        }
    }


    function maybe_apply_attachment_url($post_id, $meta_field, $exif,
                                        $exif_field) 
    {
        if (isset($exif[$exif_field])) {
            $url = $this->exif_url($exif[$exif_field]);
            // Set the metadata, which wasn't already set
            add_post_meta($post_id, $meta_field, $url, true);
        }
    }


    // Will error for image formats we can't get Exif for
    function read_exif($post_id) 
    {
        $image_path = get_attached_file($post_id);
        $exif = exif_read_data($image_path);
        return $exif;
    }

    
    // If the file has Exif, and it cointains license metadata, apply it
    function extract_exif_license_metadata($post_id) 
    {
        $exif = $this->read_exif($post_id);
        $this->maybe_apply_attachment_license_url($post_id, $exif);
        $this->maybe_apply_attachment_url(
            $post_id, 'source_url', $exif,
            'ImageDescription'
        );
    }


    function add_image_source_url($form_fields, $post) 
    {
        $post_id = $post->ID;

        //FIXME: We can get the attribution name from Artist, and title from
        //       ImageDescription.
        //       Should we?

        //FIXME: we use "credit" but that doesn't seem to be accessible?
        //       should we expose that here in some way?

        $form_fields["license_url"] = array(
           "label" => __('License&nbsp;URL'),
            "input" => "text",
            "value" => get_post_meta($post_id, 'license_url', true),
            "helps" => __("The URL for the license for the work, e.g. https://creativecommons.org/licenses/by-sa/4.0/"),
        );

        //FIXME: this should be attribution_url now we have the source work field

        $form_fields["source_url"] = array(
            "label" => __("Attribution&nbsp;URL"),
            "input" => "text",
            "value" => get_post_meta($post_id, 'source_url', true),
            "helps" => __("The URL to which the work should be attributed. For example the work's page on the author's site., e.g. https://example.com/mattl/image2/"),
        );

        $form_fields["source_work_url"] = array(
            "label" => __("Source&nbsp;Work"),
            "input" => "text",
            "value" => get_post_meta($post_id, "source_work_url", true),
            "helps" => __("The URL of the work that this work is based on or derived from, e.g. https://example.com/robm/image1/"),
        );

        $form_fields["extra_permissions_url"] = array(
            "label" => __("Extra&nbsp;Permissions"),
            "input" => "text",
            "value" => get_post_meta($post_id, "extra_permissions_url", true),
            "helps" => __("A URL where the user can find information about obtaining rights that are not already permitted by the CC license, e.g. https://example.com/mattl/image2/ccplus/"),
        );

        return $form_fields;
    }


    function save_image_source_url($post, $attachment) 
    {
        foreach (['license_url', 'source_url', 'source_work_url',
                  'extra_permissions_url'] as $field) {
            if (isset($attachment[$field])) {
                update_post_meta(
                    $post['ID'],
                    $field,
                    esc_url($attachment[$field])
                );
            }
        }
        return $post;
    }

    
    function license_block($attr, $att_id)
    {
        $license_url = get_post_meta($att_id[0], 'license_url', true);
        $license_url = strtolower($license_url);
        $attribution_url = get_post_meta($att_id[0], 'source_url', true);
        $source_work_url = get_post_meta($att_id[0], 'source_work_url', true);
        $extras_url = get_post_meta($att_id[0], 'extra_permissions_url', true);

        // Unfiltered
        $meta = wp_get_attachment_metadata($att_id[0], true);

        $title = trim($meta['image_meta']['title']);
        $credit = trim($meta['image_meta']['credit']);

        if (! $title) {
            $title = $attr['caption'];
        }

        if (strpos($license_url, "creativecommons")) {
            if (substr($license_url, -1) != "/") {
                $license_url = $license_url . "/";
            }
        }

        if ($license_url) {
            $license_name = $this->license_name($license_url);
            $button_url = $this->license_button_url($license_url);
        }

        $caption = '<div class="cc-license-caption-wrapper">'
                 . '<div class="wp-caption-text">'
                 . $attr['caption']
                 . '</div><br />';

        // RDF stuff

        if ($license_url) {
            $license_button_url = $this->license_button_url($license_url);
            $l = CreativeCommons::get_instance();
            $html_rdfa = $l->html_rdfa(
                $license_url,
                $license_name,
                $license_button_url,
                $title,
                true, // is_singular
                $attribution_url,
                $credit,
                $source_work_url,
                $extras_url,
                ''
            ); // warning_text

            $button = CreativeCommonsButton::get_instance()->markup(
                $html_rdfa,
                false,
                31,
                false
            );
            
            $caption .= $button;
            $caption .= '<!-- RDFa! -->' . $html_rdfa . "<!-- end of RDFa! -->";
        } else {
            if ($title) {
                if ($credit) {
                    $caption .= '<p>( ' . $title .' by ' . $credit . ')</p>';
                } else {
                    $caption .= '<p>( ' . $title . ')</p>';
                }
            }
            $caption .= "<p><strong>TESTING NOTE:</strong> We don't have even a CC license for this image so we made this caption instead. Go set a license in the Media Library.</p>";
        }

        $caption .= '</div>';
        
        return $caption;
    }

    
    function captionless_image ($empty, $attr, $content) {
    
    }

    
    function captioned_image($empty, $attr, $content) 
    {
        extract(
            shortcode_atts(
                array(
                    'id'=> '',
                    'align'=> 'alignnone',
                    'width'=> '',
                    'caption' => '',
                    'title' => '',
                ), $attr
            )
        );

        // Extract attachment $post->ID
        preg_match('/\d+/', $attr['id'], $att_id);

        /*if ($id) {
          $id = 'id="' . esc_attr($id) . '" ';
          }*/

        //FIXME: width is never set, and caption is always set, so test is
        //       redundant is there some logic we could implement higher up
        //       around width to exit early if width < 1 ?
        //       If not, we can add this code back in above and avoid
        //       overwriting $caption here.
        //if ((intval($width) > 1) && $caption) {
        $caption = '<div ' /*. $id*/ . 'class="cc-caption wp-caption '
                 . esc_attr($align) . '"'
                 //. ' style="width: ' . (10 + (int) $width) . 'px"'
                 . '>' . do_shortcode($content)
                 . $this->license_block($attr, $att_id)
                 . '</div>';
        //}

        return $caption;
    }

    
    function init ()
    {
        add_filter(
            'add_attachment',
            array($this, 'extract_exif_license_metadata'),
            0,
            2
        );
                   
        add_filter(
            'attachment_fields_to_edit', 
            array($this, 'add_image_source_url'),
            10,
            2
        );
        
        add_filter(
            'attachment_fields_to_save', 
            array($this, 'save_image_source_url'),
            10,
            2
        );
        
        add_filter(
            'img_caption_shortcode',
            array($this, 'captioned_image'),
            10,
            3
        );
    }
    
}
