import './style.scss';
import './editor.scss';

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks
const { InspectorControls, PanelColorSettings } = wp.editor;

/**
 * Register: Gutenberg Block.
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
registerBlockType( 'cgb/cc-by-nd', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'CC-BY-ND' ), // Block title.
	icon: 'media-text', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'cc-licenses', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	keywords: [ __( 'creative commons' ), __( 'cc-by-nd' ), __( 'license' ) ],
	attributes: {
		bgColor: {
			type: 'string',
			default: 'white',
		},
		txtColor: {
			type: 'string',
			default: 'black',
		},
	},

	/**
	 * The edit function describes the structure of your block in the context of the editor.
	 * This represents what the editor will render when the block is used.
	 *
	 * The "edit" property must be a valid function.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
	 */
	edit: function( props ) {
		const bgColor = props.attributes.bgColor;
		const txtColor = props.attributes.txtColor;

		return [
			<InspectorControls>
				<PanelColorSettings
					title={ __( 'Color Settings', 'creativecommons' ) }
					colorSettings={ [
						{
							label: __( 'Background Color' ),
							value: bgColor,
							onChange: colorValue => props.setAttributes( { bgColor: colorValue } ),
						},
						{
							label: __( 'Text Color' ),
							value: txtColor,
							onChange: colorValue => props.setAttributes( { txtColor: colorValue } ),
						},
					] }
				/>
			</InspectorControls>,

			<div className={ props.className } style={ { backgroundColor: bgColor, color: txtColor } }>
				<img src="https://licensebuttons.net/l/by-nd/3.0/88x31.png" alt="CC" />
				<p>
					This content is licensed under a{ ' ' }
					<a href="https://creativecommons.org/licenses/by-nd/4.0/">
						Creative Commons Attribution-NoDerivatives 4.0 International license.
					</a>
				</p>
			</div>,
		];
	},

	/**
	 * The save function defines the way in which the different attributes should be combined
	 * into the final markup, which is then serialized by Gutenberg into post_content.
	 *
	 * The "save" property must be specified and must be a valid function.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
	 */
	save: function( props ) {
		const bgColor = props.attributes.bgColor;
		const txtColor = props.attributes.txtColor;

		return (
			<div style={ { backgroundColor: bgColor, color: txtColor } }>
				<img src="https://licensebuttons.net/l/by-nd/3.0/88x31.png" alt="CC" />
				<p>
					This content is licensed under a{ ' ' }
					<a href="https://creativecommons.org/licenses/by-nd/4.0/">
						Creative Commons Attribution-NoDerivatives 4.0 International license.
					</a>
				</p>
			</div>
		);
	},
} );
