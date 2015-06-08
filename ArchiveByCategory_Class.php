<?php
/**
 * Plugin Name: Recents posts for current category
 * Description: Permet d'afficher les derniers articles de la catégorie en cours de visite
 * Version: 1.0
 * Author: Thibault Dumas
 */
 
class Posts_By_Category extends WP_Widget {
	
	private $font_color = 'red';

  // Configuration globale du widget
  
  function __construct() {
	  
	//Réglages propres au widget : nom de classe utilisée dans le css, description du widget dans l'admin. 
	
    $widget_args = array(
      'classname' => 'class_archive_by_category',
      'description' => 'Permet de lister des archives d\'articles correspondantes à la catégorie que nous sommes en train de visiter.'
    );
	
	//Réglages du formulaire d'administration, permet d'élargir la largeur du bloc widget dans l'admin.
	
    $control_args = array(
      'width' => 350
    );

    parent::__construct(
      'posts_by_category',
      __('Recents posts for current category'),
      $widget_args,
      $control_args
    );	
  }

	// Affichage en front-end
	function widget($args, $instance) {
		
		// Récuparation de l'id de la catégorie actuelle.
		$categories = get_the_category();
		$category_id = $categories[0]->cat_ID;
		$see_thumbnail = $instance['see_thumbnail'] == 'on' ? true : false;
		$see_home = $instance['see_home'] == 'on' ? true : false;
		
		// Si c'est la page d'accueil ou que la catégorie n'est pas trouvée nous n'affichons rien.
		if ((!is_home() & $category_id != null) || $see_home){
			echo '<style> 
					.class_archive_by_category .widget-title{  color:',$instance['font_color'],' !important;}
					.class_archive_by_category li img{
						width:20%;
						border-radius:',$instance['border_radius'],'%;
						margin-right:10px;
					}
					.class_archive_by_category li{
						margin:5px 0;
						width:100%;
						list-style:none;
					}
				  </style>';
			echo $args['before_widget'];
			if ( ! empty( $instance['title'] ) ) {
				echo $args['before_title'] , apply_filters( 'title_archive_categ', $instance['title'] ), $args['after_title'];
			}
			
			// Affichage des articles sur la sidebar sur la home, sans catégorie et par ordre décroissant
			if ($see_home & is_home()) {
				$category_id = null;
			}
			
			$posts_data = array( 'posts_per_page' => $instance['nb_posts'], 'category' => $category_id,'order' => 'DESC', 'meta_query'=>'_thumbnail_id' );
			$myposts = get_posts( $posts_data );
			foreach ( $myposts as $article ) : setup_postdata( $article );
			?>
			<li>
				<?php
						if ($see_thumbnail){
							echo get_the_post_thumbnail($article->ID);
						}
				?>
				<a href="<?php echo $article->guid; ?>"><?php echo $article->post_title; ?></a>
			</li>
			<?php endforeach; 
			wp_reset_postdata();
		}
		echo $args['after_widget'];
	}
	
	// Traitement des données avant sauvegarde
	function update( $new_instance, $old_instance ) { 
	
		$instance = array();
		
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		
		$instance['font_color'] = ( ! empty( $new_instance['font_color'] ) ) ? strip_tags( $new_instance['font_color'] ) : '';
		
		$instance['nb_posts'] = ( ! empty( $new_instance['nb_posts'] ) ) ? strip_tags( $new_instance['nb_posts'] ) : '';
		if ($instance['nb_posts']>10 || $instance['nb_posts']<0){
			$instance['nb_posts'] = 3;
		}
		
		$instance['border_radius'] = ( ! empty( $new_instance['border_radius'] ) ) ? strip_tags( $new_instance['border_radius'] ) : '';
		
		//$instance['see_thumbnail'] = ( ! empty( $new_instance['see_thumbnail'] ) ) ? strip_tags( $new_instance['see_thumbnail'] ) : '';
		$instance['see_thumbnail'] = $new_instance['see_thumbnail'];		
		$instance['see_home'] = $new_instance['see_home'];
		
		
		return $instance;
	}

	// Affichage du formulaire de réglages du widget en back-end
	function form($instance) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'New title', 'text_domain' );
		$nb_posts = ! empty( $instance['nb_posts'] ) ? $instance['nb_posts'] : __( '3', 'text_domain' );
		$this->font_color = ! empty( $instance['font_color'] ) ? $instance['font_color'] : __( '#CC99CC', 'text_domain' );
		$border_radius = ! empty( $instance['border_radius'] ) ? $instance['border_radius'] : __( '0', 'text_domain' );
		?>
		<style>
			.wp-picker-container{
				vertical-align: middle;
			}
		</style>
		<script type="text/javascript">
            jQuery(document).ready(function($){
                $('#<?php echo $this->get_field_id( 'font_color' ); ?>').wpColorPicker();
            });
		</script>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input  id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>	
			<label for="<?php echo $this->get_field_id( 'font_color' ); ?>" style="display:innline-block; vertical-align:middle;"><?php _e( 'Couleur du titre :' ); ?></label> 
			<input style="vertical-align:middle; display:inline-block;" class="color-field" id="<?php echo $this->get_field_id( 'font_color' ); ?>" name="<?php echo $this->get_field_name( 'font_color' ); ?>" type="text" value="<?php echo esc_attr( $this->font_color ); ?>" />
		</p>	
		<p>	
			<label for="<?php echo $this->get_field_id( 'nb_posts' ); ?>"><?php _e( 'Nombre d\'articles à afficher:' ); ?></label> 
			<input size="3" id="<?php echo $this->get_field_id( 'nb_posts' ); ?>" name="<?php echo $this->get_field_name( 'nb_posts' ); ?>" type="text" value="<?php echo esc_attr( $nb_posts ); ?>">	
		</p>
		<p>	
			<label for="<?php echo $this->get_field_id( 'border_radius' ); ?>"><?php _e( 'Arrondi des images (en %) :' ); ?></label> 
			<input size="3" id="<?php echo $this->get_field_id( 'border_radius' ); ?>" name="<?php echo $this->get_field_name( 'border_radius' ); ?>" type="text" value="<?php echo esc_attr( $border_radius ); ?>">	
		</p>
		<p>	
			<label for="<?php echo $this->get_field_id( 'see_thumbnail' ); ?>"><?php _e( 'Afficher les images à la une :' ); ?></label> 
			<input <?php checked($instance['see_thumbnail'], 'on'); ?> id="<?php echo $this->get_field_id( 'see_thumbnail' ); ?>" name="<?php echo $this->get_field_name( 'see_thumbnail' ); ?>" type="checkbox">	
		</p>
		<p>	
			<label for="<?php echo $this->get_field_id( 'see_home' ); ?>"><?php _e( 'Affichage sur la page d\'accueil :' ); ?></label> 
			<input <?php checked($instance['see_home'], 'on'); ?> id="<?php echo $this->get_field_id( 'see_home' ); ?>" name="<?php echo $this->get_field_name( 'see_home' ); ?>" type="checkbox">	
		</p>
		
		<?php
	}
}




function posts_by_categ_widget() {
  register_widget('Posts_By_Category');
}

add_action('widgets_init', 'posts_by_categ_widget');
add_action( 'admin_enqueue_scripts', 'wptuts_add_color_picker' );


function wptuts_add_color_picker( $hook ) {
    if( is_admin() ) { 
        // Add the color picker css file       
        wp_enqueue_style( 'wp-color-picker' ); 
        // Include our custom jQuery file with WordPress Color Picker dependency
        wp_enqueue_script( 'custom-script-handle', plugins_url( 'custom-script.js', __FILE__ ), array( 'wp-color-picker' ), false, true ); 
    }
}
