import { useBlockProps } from "@wordpress/block-editor";
import { registerBlockType } from "@wordpress/blocks";
import metadata from "./block.json";

registerBlockType(metadata, {
	edit: EditComponent,
	save: SaveComponent,
});

function EditComponent() {
	const blockProps = useBlockProps({
		className: "language-switcher",
	});

	return (
		<div {...blockProps}>
			<a className="lang-btn active" hrefLang="en">EN</a>
			<span className="lang-separator" aria-hidden="true">|</span>
			<a className="lang-btn" hrefLang="fr">FR</a>
		</div>
	);
}

// PHP render.php handles the real output
function SaveComponent() {
	return null;
}
