import { useBlockProps } from "@wordpress/block-editor";
import { registerBlockType } from "@wordpress/blocks";
import metadata from "./block.json";

registerBlockType(metadata, { edit: EditComponent, save: SaveComponent });

function EditComponent() {
	const blockProps = useBlockProps({ className: "pdf-buttons" });
	return (
		<div {...blockProps}>
			<a className="pdf-btn pdf-btn--download" href="#">
				<span>Télécharger le PDF</span>
			</a>
			<button className="pdf-btn pdf-btn--view">
				<span>Voir le PDF</span>
			</button>
		</div>
	);
}

function SaveComponent() {
	return null;
}
