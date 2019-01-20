<?php
/**
 * SPWP_Frontend Class.
 *
 * @class       SPWP_Frontend
 * @version		1.0.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * SPWP_Frontend class.
 */
class SPWP_Frontend {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function instance() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new SPWP_Frontend();
        }

        return $instance;
    }

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->includes();

		add_filter( 'script_loader_tag', array( $this, 'change_script_loader_tag' ), 100, 3 );
		add_filter( 'style_loader_tag', array( $this, 'change_style_loader_tag' ), 100, 3 );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		// add_action( 'wp_loaded', array( $this, 'move_footer_scripts' ), 100 );
		add_action( 'wp_head', array( $this, 'add_loadcss_js' ), 10);
	}

	public function change_script_loader_tag( $tag, $handle, $src ){
		global $wp_scripts;

	 	// @todo | conditional from option
		$should_change = apply_filters( 'spwp_should_change_script_tag', true, $handle );

		if( $should_change ){
			$data = $wp_scripts->registered[$handle]->deps;
			$tag = str_replace(' src', ' defer="defer" src', $tag);
		}

		return $tag;
	}

	public function change_style_loader_tag( $tag, $handle, $src ){
		global $wp_styles;

		// @todo | conditional from option
		$should_change = apply_filters( 'spwp_should_change_style_tag', true, $handle );
		
		if( $should_change ){
			$rel = $wp_styles->get_data( $handle, 'alt' ) ? 'alternate stylesheet' : 'stylesheet';
			$tag = str_replace(" rel='".$rel."'", " rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"", $tag);
		}

		return $tag;
	}

	public function enqueue_scripts(){

		if( SPWP()->is_request( 'frontend' ) ){
 			wp_enqueue_script( 'spwp' );
 			wp_localize_script( 'spwp', 'SPWP_VARS', array(  
 				'admin_url' => admin_url()
 			) );
 		}
	}

	public function move_footer_scripts(){

		$action_wp_print_footer_scripts = has_action( 'wp_footer', 'wp_print_footer_scripts' );
		if( $action_wp_print_footer_scripts ){
			remove_action( 'wp_footer', 'wp_print_footer_scripts', $action_wp_print_footer_scripts );
			add_action( 'wp_head', 'wp_print_footer_scripts', 100 );
		}
	}

	public function add_loadcss_js(){
		?>
		<script id="load-css">
			/*! loadCSS. [c]2017 Filament Group, Inc. MIT License */
			/* This file is meant as a standalone workflow for
			- testing support for link[rel=preload]
			- enabling async CSS loading in browsers that do not support rel=preload
			- applying rel preload css once loaded, whether supported or not.
			*/
			(function( w ){
				"use strict";
				// rel=preload support test
				if( !w.loadCSS ){
					w.loadCSS = function(){};
				}
				// define on the loadCSS obj
				var rp = loadCSS.relpreload = {};
				// rel=preload feature support test
				// runs once and returns a function for compat purposes
				rp.support = (function(){
					var ret;
					try {
						ret = w.document.createElement( "link" ).relList.supports( "preload" );
					} catch (e) {
						ret = false;
					}
					return function(){
						return ret;
					};
				})();

				// if preload isn't supported, get an asynchronous load by using a non-matching media attribute
				// then change that media back to its intended value on load
				rp.bindMediaToggle = function( link ){
					// remember existing media attr for ultimate state, or default to 'all'
					var finalMedia = link.media || "all";

					function enableStylesheet(){
						link.media = finalMedia;
					}

					// bind load handlers to enable media
					if( link.addEventListener ){
						link.addEventListener( "load", enableStylesheet );
					} else if( link.attachEvent ){
						link.attachEvent( "onload", enableStylesheet );
					}

					// Set rel and non-applicable media type to start an async request
					// note: timeout allows this to happen async to let rendering continue in IE
					setTimeout(function(){
						link.rel = "stylesheet";
						link.media = "only x";
					});
					// also enable media after 3 seconds,
					// which will catch very old browsers (android 2.x, old firefox) that don't support onload on link
					setTimeout( enableStylesheet, 3000 );
				};

				// loop through link elements in DOM
				rp.poly = function(){
					// double check this to prevent external calls from running
					if( rp.support() ){
						return;
					}
					var links = w.document.getElementsByTagName( "link" );
					for( var i = 0; i < links.length; i++ ){
						var link = links[ i ];
						// qualify links to those with rel=preload and as=style attrs
						if( link.rel === "preload" && link.getAttribute( "as" ) === "style" && !link.getAttribute( "data-loadcss" ) ){
							// prevent rerunning on link
							link.setAttribute( "data-loadcss", true );
							// bind listeners to toggle media back
							rp.bindMediaToggle( link );
						}
					}
				};

				// if unsupported, run the polyfill
				if( !rp.support() ){
					// run once at least
					rp.poly();

					// rerun poly on an interval until onload
					var run = w.setInterval( rp.poly, 500 );
					if( w.addEventListener ){
						w.addEventListener( "load", function(){
							rp.poly();
							w.clearInterval( run );
						} );
					} else if( w.attachEvent ){
						w.attachEvent( "onload", function(){
							rp.poly();
							w.clearInterval( run );
						} );
					}
				}


				// commonjs
				if( typeof exports !== "undefined" ){
					exports.loadCSS = loadCSS;
				}
				else {
					w.loadCSS = loadCSS;
				}
			}( typeof global !== "undefined" ? global : this ) );
		</script>
		<?php
	}

	public function includes(){
	
	}

}

SPWP_Frontend::instance();