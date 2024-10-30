/**
 * BLOCK: custom-posts-list
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

//  Import CSS.
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks
const { SelectControl, RangeControl, PanelBody, PanelRow, ToggleControl } = wp.components;
const { InspectorControls, useBlockProps, AlignmentToolbar, BlockControls, ColorPalette } = wp.blockEditor;
const { serverSideRender: ServerSideRender } = wp;

/**
 * Register: aa Gutenberg Block.
 *
 * Registers a new block provided a unique name and an object defining its
 * behavior. Once registered, the block is made editor as an option to any
 * editor interface where blocks are implemented.
 *
 * @link https://wordpress.org/gutenberg/handbook/block-api/
 * @param  {string}   name     Block name.
 * @param  {Object}   settings Block settings.
 * @return {?WPBlock}          The block, if it has been successfully
 *                             registered; otherwise `undefined`.
 */
registerBlockType( 'cm/custom-posts-list', {
	apiVersion: 2,
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'Custom Posts List' ), // Block title.
	icon: 'shield', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'common', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	keywords: [
		__( 'Latest Posts' ),
		__( 'Recent Posts' ),
		__( 'New Posts' ),
	],

	attributes: {
		categories: {
			type: 'object'
		},
		selectedCategory: {
			type: 'integer',
			default: 1
		},
		postsPerPage: {
			type: 'integer',
			default: 5
		},
		showImage: {
			type: 'boolean',
			default: 1,
		},
		showCategoryList: {
			type: 'boolean',
			default: 1,
		},
		showExcerpt: {
			type: 'boolean',
			default: 1
		},
		showReadMore: {
			type: 'boolean',
			default: 1
		},
		excerptLength: {
			type: 'integer',
			default: 100
		},
		textAlign: {
			type: 'string',
			default: 'left'
		},
		bgColor: {
			type: 'string',
			default: '#fff'
		}
	},

	/**
	 * The edit function describes the structure of your block in the context of the editor.
	 * This represents what the editor will render when the block is used.
	 *
	 * The "edit" property must be a valid function.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
	 *
	 * @param {Object} props Props.
	 * @returns {Mixed} JSX Component.
	 */
	edit: function( props ){

		if( !props.attributes.categories ){
			wp.apiFetch( {
				url: cmplGlobal.site_url + '/wp-json/wp/v2/categories?per_page=100'
			} ).then( categories => {
				props.setAttributes( {
					categories: categories
				} )
			});
		}

		if( props.attributes.categories && props.attributes.categories.length === 0 ){
			return __( 'No categories found. Please add some!' );
		}

		function getCategories(){
			let categories = [];
			props.attributes.categories.forEach( (item, index, array) => {
				categories.push( { value: item.id, label: item.name }  );
			} )

			return categories;
		}

		const blockProps = useBlockProps();
		
		const { categories, ...propsForServer } = props.attributes;

		return ([
			<InspectorControls>
                <PanelBody title={ __( 'Posts Settings' ) }>
                    
					<PanelRow>
						<SelectControl
							label={ __( 'Category:' ) }
							value={ props.attributes.selectedCategory } 
							style={ {width: "100%" } }
							onChange={ ( value ) => props.setAttributes( { selectedCategory: parseInt( value ) } ) }
							options={ getCategories() }
						/>
					</PanelRow>

					<PanelRow>
						<RangeControl
							value={ props.attributes.postsPerPage }
							style={ { width: "100%" } }
							label={ __( 'Posts Per Page:' ) }
							min={ 2 }
							max={ 20 }
							onChange={ ( value ) => props.setAttributes( { postsPerPage: parseInt( value ) } ) } />
					</PanelRow>
					
					<PanelRow>
						<ToggleControl
							label={
								props.attributes.showImage
									? __('Hide Image')
									: __('Show Image')
							}
							checked={ props.attributes.showImage }
							onChange={ ( value ) => props.setAttributes( { showImage: value } ) }
						/>						
					</PanelRow>

					<PanelRow>
						<ToggleControl
							label={
								props.attributes.showCategoryList
									? __('Hide Category List')
									: __('Show Category List')
							}
							checked={ props.attributes.showCategoryList }
							onChange={ ( value ) => props.setAttributes( { showCategoryList: value } ) }
						/>
					</PanelRow>

					<PanelRow>
						<ToggleControl
							label={
								props.attributes.showExcerpt
									? __('Hide Excerpt')
									: __('Show Excerpt')
							}
							checked={ props.attributes.showExcerpt }
							onChange={ ( value ) => props.setAttributes( { showExcerpt: value } ) }
						/>
					</PanelRow>

					{ /* Conditional InspectorControl */ }
					{ 
						props.attributes.showExcerpt == true && (
							<PanelRow>
								<RangeControl
								value={ props.attributes.excerptLength }
								style={ {width: "100%" } }
								label={ __( 'Excerpt Length:' ) }
								min={ 50 }
								max={ 250 }
								step={ 50 }
								type={ 'stepper' }
								onChange={ ( value ) => props.setAttributes( { excerptLength: parseInt( value ) } ) } />
							</PanelRow>	
						)
					}

					<PanelRow>
						<ToggleControl
							label={
								props.attributes.showReadMore
									? __('Hide Read More')
									: __('Show Read More')
							}
							checked={ props.attributes.showReadMore }
							onChange={ ( value ) => props.setAttributes( { showReadMore: value } ) }
						/>
					</PanelRow>

					<PanelRow>
						<ColorPalette
							colors={ [
								{ name: 'red', color: '#f00' },
								{ name: 'green', color: '#0f0' },
								{ name: 'blue', color: '#00f' },
							] }
							value={ props.attributes.bgColor }
							style={ {width: "100%" } }
							onChange={ ( value ) => props.setAttributes( { bgColor: value } ) }
						/>
					</PanelRow>

					{ /* This will show above the block beside the block icon */ }
					<BlockControls>
						<AlignmentToolbar
							value={ props.attributes.textAlign }
							onChange={ ( value ) => props.setAttributes( { textAlign: value } ) }
						/>
					</BlockControls>

                </PanelBody>
            </InspectorControls>,			

			<div { ...blockProps }>
				<ServerSideRender
					block="cm/custom-posts-list"
					attributes={ propsForServer  }
				/>
			</div>
		]			
		);

	} ,

	/**
	 * The save function defines the way in which the different attributes should be combined
	 * into the final markup, which is then serialized by Gutenberg into post_content.
	 *
	 * The "save" property must be specified and must be a valid function.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
	 *
	 * @param {Object} props Props.
	 * @returns {Mixed} JSX Frontend HTML.
	 */
	save: props => {
		return null;
	},
} );
