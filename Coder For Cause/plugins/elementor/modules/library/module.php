<?php
namespace Elementor\Modules\Library;

use Elementor\Core\Base\Module as BaseModule;
use Elementor\Modules\Library\Documents;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor library module.
 *
 * Elementor library module handler class is responsible for registering and
 * managing Elementor library modules.
 *
 * @since 2.0.0
 */
class Module extends BaseModule {

	/**
	 * Get module name.
	 *
	 * Retrieve the library module name.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return string Module name.
	 */
	public function get_name() {
		return 'library';
	}

	/**
	 * Library module constructor.
	 *
	 * Initializing Elementor library module.
	 *
	 * @since 2.0.0
	 * @access public
	 */
	public function __construct() {
		Plugin::$instance->documents
			->register_document_type( 'not-supported', Documents\Not_Supported::get_class_full_name() )
			->register_document_type( 'page', Documents\Page::get_class_full_name() )
			->register_document_type( 'section', Documents\Section::get_class_full_name() )
			->register_group( 'blocks', [
				'label' => __( 'Blocks', 'elementor' ),
			] )->register_group( 'pages', [
				'label' => __( 'Pages', 'elementor' ),
			] );
	}
}
