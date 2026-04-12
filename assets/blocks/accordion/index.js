import {
	InnerBlocks,
	RichText,
	InspectorControls,
	useBlockProps,
} from "@wordpress/block-editor";
import { PanelBody, PanelRow, ToggleControl } from "@wordpress/components";
import { registerBlockType } from "@wordpress/blocks";
import metadata from "./block.json";

registerBlockType(metadata, {
	edit: EditComponent,
	save: SaveComponent,
});

function EditComponent(props) {
	const { attributes, setAttributes } = props;
	const { title, text, open } = attributes;

	function handleTitleChange(value) {
		setAttributes({ title: value });
	}

	function handleTextChange(value) {
		setAttributes({ text: value });
	}

	function handleOpenChange(value) {
		setAttributes({ open: value });
	}

	const blockProps = useBlockProps({
		className: "accordion",
	});

	return (
		<>
			<InspectorControls>
				<PanelBody title="Open settings" initialOpen={true}>
					<PanelRow>
						<ToggleControl
							label="Open toggle"
							help={
								open
									? "Will be open by default."
									: "Will be closed by default."
							}
							checked={open}
							onChange={handleOpenChange}
						/>
					</PanelRow>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<div className="accordionToggle wp-block wp-block-group is-layout-flex">
					<RichText
						tagName="span"
						className="title"
						value={title}
						onChange={handleTitleChange}
					/>

					<RichText
						tagName="span"
						className="toggle"
						value={text}
						onChange={handleTextChange}
					/>
				</div>

				<div className="accordionContent">
					<InnerBlocks />
				</div>
			</div>
		</>
	);
}

// Dynamic block → return null
function SaveComponent() {
	return <InnerBlocks.Content />;
}