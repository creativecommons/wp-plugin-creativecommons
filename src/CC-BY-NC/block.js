import './style.scss';
import './editor.scss';
import globals from 'cgbGlobal';
const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks
const { InspectorControls, PanelColorSettings } = wp.editor; // Import color settings from wp.editor
const { RichText } = wp.editor; // Import RichText blocks from wp.editor

/**
 * Register: CC-BY-NC Gutenberg block.
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
registerBlockType('cgb/cc-by-nc', {
	title: __('CC-BY-NC'),
	icon: 'media-text',
	category: 'cc-licenses',
	keywords: [__('creative commons'), __('CC-BY-NC'), __('attribution')],
	attributes: {
		bgColor: {
			type: 'string',
			default: 'white'
		},
		txtColor: {
			type: 'string',
			default: 'black'
		},
		contentName: {
			selector: '.cc-cgb-name',
			source: 'children'
		},
		contentText: {
			selector: '.cc-cgb-text',
			source: 'children'
		}
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

				<img src={`${globals.pluginDirUrl}includes/images/by-nc.png`} alt="CC-BY-NC" />
				<p>
					This content is licensed under a{ ' ' }
					<a href="https://creativecommons.org/licenses/by-nc/4.0">
						Creative Commons Attribution-NonCommercial 4.0 International license.
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
				<img src={`${globals.pluginDirUrl}includes/images/by-nc.png`} alt="CC-BY-NC" />
				<p>
					This content is licensed under a{ ' ' }
					<a href="https://creativecommons.org/licenses/by-nc/4.0">
						Creative Commons Attribution-NonCommercial 4.0 International license.
					</a>
				</p>
			</div>
		);
	},
} );
