<?php
/**
    Plugin name: Posts in Page
    Author: dgilfoy, ivycat
    Description: Add Posts in page
    Version: 1.0.0
    
    Shortcode usage:
    [ic_add_posts]  - Add all posts to a page (limit to what number posts in wordpress is set to), essentially adds blog "page" to page.
    [ic_add_posts category='category-slug']  - Show posts within a specific category.  Uses slugs, can have multiple but separate by commas. category-1,category2, etc (no spaces.)
    [ic_add_posts post_type='post-type'] - Show posts that are a specific post type (only one post type right now)
    [ic_add_posts tax='taxonomy' term='term'] - limit posts to those that exist in a taxonomy and have a specific term.  Both are required for either one to work
    [ic_add_posts template='template-in-theme-dir.php'] - In case you want to style your markup, add meta data, etc.  Each shortcode can reference a different template.  These templates must exist in the theme directory.
**/

class AddPostsToPage{
    
    protected $args;
    
    public function __construct(){
        add_shortcode( 'ic_add_posts', array( &$this, 'posts_in_page' ) );
    }
    
    public function posts_in_page( $atts ){
        extract( shortcode_atts( array(
            'category' => false,
            'post_type' => false,
            'tax' => false,
            'term' => false,
            'tag' => false,
            'template' => false
        ), $atts ) );
        self::set_args( $atts );
        return self::output_posts();
    }
    
    protected function output_posts(){
        $page_posts = new WP_Query( $this->args );
        $output = '';
        if( $page_posts->have_posts() ): while( $page_posts->have_posts()):
        $output .= self::add_template_part( $page_posts );
        endwhile; endif;
        wp_reset_postdata();
        return $output;
    }
    
    protected function set_args( $atts ){
        global $wp_query;
        $this->args = array( 'post_type' => (  $atts['post_type'] ) ? $atts['post_type'] : 'post' );
        if( $atts['category'] ){
            $cats = explode( ',', $atts['category'] );
            $this->args['category_name'] = ( count( $cats ) > 1 ) ? $cats : $atts['category'];
        }
        if( $atts['tax'] ){
            if( $atts['term'] ){
                $terms = explode( ',', $atts['term'] );
                $this->args['tax_query'] = array(
                    array( 'taxonomy' => $atts['tax'], 'field' => 'slug', 'terms' => ( count( $terms ) > 1 ) ? $terms : $atts['term'] )
                );
            }
        }
        if( $atts['tag'] ){
            $tags = explode( ',', $atts['category'] );
            $this->args['tag'] = ( count( $tags ) > 1 ) ? $tags : $atts['tag'];
        }
        if( $wp_query->query_vars['page'] > 1 ){
            $this->args['paged'] = $wp_query->query_vars['page'];
        }
    }
    
    protected function has_theme_template(){
        $template_file = ( $this->args['template'] ) ? get_theme_root() . '/' . $this->args['template'] : get_theme_root() . '/posts_loop_template.php';
        return ( file_exists( $template_file ) ) ? $template_file : false;
    }
    
   protected function add_template_part( $ic_posts ){
      $ic_posts->the_post();
      ob_start();
      require ( $file_path = self::has_theme_template() ) ? $file_path : 'posts_loop_template.php';
      $output .= ob_get_contents();
      return ob_get_clean();
   }
    
} new AddPostsToPage();