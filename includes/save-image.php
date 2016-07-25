<?php
/*
  Author: Bjorn Wijers <burobjorn@burobjorn.nl>, Tarmo Toikkanen <tarmo@iki.fi>, Matt Lee <mattl@creativecommons.org>, Rob Myers <rob@creativecommons.org>
  License: GPLv2 or later versions
*/

// Convert a structured English representation of a license into a license url
// $copyright has already been lowercased at this point

function cc_copyright_license_url($copyright) {
    $url = false;
    $matched = preg_match('/creative commons attribution (\D*)(\d\.\d)/',
                          $copyright, $matches);
    if ($matched) {
        $url = 'http://creativecommons.org/licenses/by';
        if (strpos($matches[1], ' noncommercial') !== false) {
            $url .= '-nc';
        }
        if (strpos($matches[1], ' noderivatives') !== false) {
            $url .= '-nd';
        }
        if (strpos($matches[1], ' sharealike') !== false) {
            $url .= '-sa';
        }
        $url .= '/' . $matches[2] . '/';
    }
    return $url;
}

// Convert a license url into the url for the icon for that license

function cc_license_button_url ($license_url) {
    $url = false;
    $matched = preg_match('/\/(licenses|publicdomain)\/([^\/]+)\//',
                          $license_url, $matches);
    if ($matched) {
        $button = $matches[2];
        if ($matches[1] == 'publicdomain') {
            $button = 'pd';
        }
        $url = 'https://licensebuttons.net/l/' . $button . '/4.0/88x31.png';
    }
    return $url;
}

// generate the canonical English name for the license with the given url

function cc_license_name ($license_url) {
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

add_filter("attachment_fields_to_edit", "add_image_source_url", 10, 2);
function add_image_source_url($form_fields, $post) {

    //FIXME: we use "credit" but that doesn't seem to be accessible?
    //       should we expose that here in some way?

    $form_fields["license_url"] = array(
        "label" => __("License URL"),
        "input" => "text",
        "value" => get_post_meta($post->ID, "license_url", true),
        "helps" => __("The URL for the license for the work, e.g. https://creativecommons.org/licenses/by-sa/4.0/"),
    );

    //FIXME: this should be attribution_url now we have the source work field
    //       it should also have the help "Add the URL to which the work should be attributed. For example, the work's page on the author's site."

    $form_fields["source_url"] = array(
        "label" => __("Atribution URL"),
        "input" => "text",
        "value" => get_post_meta($post->ID, "source_url", true),
        "helps" => __("The URL where the original image was posted"),
    );

    $form_fields["source_work_url"] = array(
        "label" => __("Source Work URL"),
        "input" => "text",
        "value" => get_post_meta($post->ID, "source_work_url", true),
        "helps" => __("The URL of the work that this work is based on or derived from"),
    );

    $form_fields["extra_permissions_url"] = array(
        "label" => __("Extra Permissions URL"),
        "input" => "text",
        "value" => get_post_meta($post->ID, "extra_permissions_url", true),
        "helps" => __("A URL where the user can find information about obtaining rights that are not already permitted by the CC license"),
    );

    return $form_fields;
}

add_filter("attachment_fields_to_save", "save_image_source_url", 10 , 2);
function save_image_source_url($post, $attachment) {
    foreach (['license_url', 'source_url', 'source_work_url',
              'extra_permissions_url'] as $field) {
        if (isset($attachment[$field]))
            update_post_meta($post['ID'], $field, esc_url($attachment[$field]));
    }
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
    $source_work_url = get_post_meta($att_id[0], 'source_work_url', true);
    $extras_url = get_post_meta($att_id[0], 'extra_permissions_url', true);

    $meta = wp_get_attachment_metadata($att_id[0]);

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

    if (! $license_url) {
        $copyright = strtolower($meta['image_meta']['copyright']);
        $license_url = cc_copyright_license_url ($copyright);
    }

    if ($license_url) {
        $license_name = cc_license_name($license_url);
        $button_url = cc_license_button_url($license_url);
    }

    $caption .= '<div class="cc-copyright wp-caption-text" style="background: yellow; border: 1px solid red; padding: 1em;">';

    // RDF stuff

    if ($license_url) {
        $license_button_url = cc_license_button_url($license_url);
        $l = new CreativeCommons;
        $html_rdfa = $l->html_rdfa($license_url,
                                   $license_name,
                                   $license_button_url,
                                   $title,
                                   true, // is_singular
                                   $source_url,
                                   $credit,
                                   $source_work_url,
                                   $extras_url,
                                   ''); // warning_text

        $button = CCButton::markup($html_rdfa, false, 31, false);
        $caption .= $button;
        $caption .= '<!-- RDFa! -->' . $html_rdfa .= "<!-- end of RDFa! -->";
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

    if ($id) {
        $id = 'id="' . esc_attr($id) . '" ';
    }

    //FIXME: width is never set, and caption is always set, so test is redundant
    //       is there some logic we could implement higher up around width to
    //       exit early if width < 1 ?
    //       If not, we can add this code back in above and avoid overwriting
    //       $caption here.
    //if ((intval($width) > 1) && $caption) {
    $caption = '<div ' . $id . 'class="cc-caption wp-caption '
             . esc_attr($align) . '" style="width: ' . (10 + (int) $width)
             . 'px">' . do_shortcode($content)
             . '<p class="wp-caption-text">' . $caption . '</p></div>';
    //}

    return $caption;
}
