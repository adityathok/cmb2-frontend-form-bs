<?php
//CMB2 Init for custom post type "produk"
add_action( 'cmb2_init', 'produk_register_metabox' );
function produk_register_metabox() {
    $cmb = new_cmb2_box( array(
        'id'           => 'produk_metabox',
        'title'        => 'Detail Produk',
        'object_types' => array( 'produk'),
    ) );
    
    $cmb->add_field( array(
        'name' => 'Nama Produk',
        'desc' => '',
        'id'   => 'post_title',
        'type' => 'text',
    ) );
    $cmb->add_field( array(
        'name' => 'Harga',
        'desc' => 'Tanpa Rp',
        'id'   => 'harga',
        'type' => 'text',
    ) );
    $cmb->add_field( array(
        'name' => 'Deskripsi',
        'desc' => '',
        'id'   => 'post_content',
        'type' => 'wysiwyg',
        'options' => array(
            'media_buttons' => false,
        ),
    ) );
    $cmb->add_field( array(
        'name' => 'Foto',
        'desc' => '',
        'id'   => 'post_thumbnail',
        'type' => 'file',
    ) );
    $cmb->add_field( array(
        'name'           => 'Kategori',
        'desc'           => 'Description Goes Here',
        'id'             => 'kategori-produk',
        'taxonomy'       => 'kategori-produk',
        'type'           => 'taxonomy_select',
        'remove_default' => 'true',
        'query_args' => array(
            // 'orderby' => 'slug',
            'hide_empty' => false,
        ),
    ) );
}
