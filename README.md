# How to use this theme to build a new WP Project
***

## Design to HTML {#html}
***

1. Start by forking this repo as new repo and name it "**PROJECT-NAME** - WP Theme".
2. Clone the newly created repo to your local environment to start working on it.
3. [Setup your fonts](#markdown-header-setup-fonts).
4. Edit the [CLIENT] in the style.css file to be the name of the current client.

### You can now start building the HTML, CSS, and JS related to your blocks ###
***

**I would like you to follow the following format when creating each block.**

1. Create a new folder inside of /template-parts/blocks/ with the title of the block you are building.
2. Create HTML, CSS, and JS (if needed) files with the same title. (follow the format of the hero block file structure)
3. Inside your HTML file, you can import boiletplate HTML 5 code by typing "html:5" and hitting enter.
4. Copy paste the fonts.css, style.css, and block CSS files into the HEAD.
5. Copy paste whichever JS files you may need (GSAP, etc) and your block JS file below your HTML code before the </body> closing tag.

## HTML to WP {#wp}
***

1. Create a folder named "utopian" in the "themes" folder on your local WordPress installation.
2. Clone the repo into the new folder "utopian" to start working on it.
3. The fonts should already be setup, but if the HTML uses a Google font, you will need to setup the fonts from the html files into the functions.php.
4. You can then start building the header and footer of your project.
5. Proceed after by converting all html block files into php files. Once a block is complete, delete the html version.

## Extras
***

### Setup fonts {#fonts}
***

#### Google fonts
***

1. If the project uses Google fonts, you can delete the fonts folder and just go to [Google Fonts](https://fonts.google.com/) and select all fonts needed and use the link in the head file to grab the fonts.
2. Change the font family variables in the style.css file to the ones you've added from Google.
3. Set the maximum font size that you can find in the file. For example look at a normal paragraph font size and use that as your font size in the @media (min-width: 1700px) and then fix the calculation for font-size in the @media (min-width: 960px).

**Notes: Use EMs at all times for all font-sizes. Also please do not use max-widths for the wraps anymore, lets use margins with ems to keep a perfect responsive design throughout. If you believe it isn't possible to build a block without using a max-width for the wrap or container, please let me know so we can discuss it.**

#### If it's not a google font
***

1. Use <a href="https://transfonter.org/">Transfonter.org</a> to generate your font files and replace the font files currently found in the fonts folder.
2. Change the font family variables in the style.css file to the ones you've added from Transfonter.
3. Set the maximum font size that you can find in the file. For example look at a normal paragraph font size and use that as your font size in the @media (min-width: 1700px) and then fix the calculation for font-size in the @media (min-width: 960px).

**Notes: Use EMs at all times for all font-sizes. Also please do not use max-widths for the wraps anymore, lets use margins with ems to keep a perfect responsive design throughout. If you believe it isn't possible to build a block without using a max-width for the wrap or container, please let me know so we can discuss it.**