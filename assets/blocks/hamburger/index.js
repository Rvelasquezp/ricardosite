import { useBlockProps } from "@wordpress/block-editor";
import { registerBlockType } from "@wordpress/blocks";
import metadata from "./block.json";

registerBlockType(metadata, {
	edit: EditComponent,
	save: SaveComponent,
});

function EditComponent(props) {
	const blockProps = useBlockProps({
		className: "main-menu-toggler mobile-only",
	});

	return (
		<button {...blockProps}>
			<div class="main-menu-toggler-button open">
				<div class="hamburger">
					<span class="top"></span>
					<span class="middle"></span>
					<span class="bottom"></span>
				</div>
			</div>
		</button>
	);
}

// If you want your block to use a php callback return null.
function SaveComponent() {
	return null;
}
