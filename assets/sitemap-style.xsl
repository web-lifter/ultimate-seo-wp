<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" indent="yes"/>

    <xsl:template match="/">
        <html>
        <head>
            <meta charset="UTF-8"/>
            <title>Ultimate SEO WP Sitemap</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; background-color: #f9f9f9; }
                table { width: 100%; border-collapse: collapse; background: white; }
                th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
                th { background-color: #0073aa; color: white; }
                a { text-decoration: none; color: #0073aa; }
                a:hover { text-decoration: underline; }
                .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Ultimate SEO WP Sitemap</h1>
                <p>This is a dynamically generated sitemap for:</p>
                <p><strong><xsl:value-of select="/urlset/url[1]/loc"/></strong></p>
                
                <table>
                    <tr>
                        <th>URL</th>
                        <th>Last Modified</th>
                    </tr>
                    <xsl:for-each select="/urlset/url">
                        <tr>
                            <td>
                                <a href="{loc}" target="_blank">
                                    <xsl:value-of select="loc"/>
                                </a>
                            </td>
                            <td>
                                <xsl:value-of select="lastmod"/>
                            </td>
                        </tr>
                    </xsl:for-each>
                </table>
            </div>
        </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
