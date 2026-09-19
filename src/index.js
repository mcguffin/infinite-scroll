import { __ } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';
import { InspectorControls } from '@wordpress/block-editor';
import { ExternalLink, PanelBody, PanelRow, ToggleControl } from '@wordpress/components';


addFilter(
	'editor.BlockEdit',
	'mcguffin/infinite-scroll',
	BlockEdit => {
		return ( props ) => {
			const { name, attributes, setAttributes } = props;

			// Early return if the block is not the Image block.
			if ( name !== 'core/post-template' ) {
				return <BlockEdit { ...props } />;
			}

			// Retrieve selected attributes from the block.
			const { alt, isInfiniteScroll } = attributes;

			return (
				<>
					<BlockEdit { ...props } />
					<InspectorControls>
						<PanelBody
							title={ __(
								'Navigation',
								'enable-infinite-scroll'
							) }
						>
							<PanelRow>
								<ToggleControl
									label={ __(
										'Enable Infinite Scroll',
										'enable-infinite-scroll'
									) }
									checked={ isInfiniteScroll }
									onChange={ () => {
										setAttributes( {
											isInfiniteScroll: ! isInfiniteScroll
										} );
									} }
								/>
							</PanelRow>
						</PanelBody>
					</InspectorControls>
				</>
			);
		};
	}
);