<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:sm="http://www.sitemaps.org/schemas/sitemap/0.9">
<xsl:output method="html" encoding="UTF-8" indent="yes"/>

<!-- sitemapfix-20260723: the XML now carries ONLY standard sitemap tags (GSC flagged the
     old chee: namespace as "Invalid XML tag" x292). Title and Type are derived from the URL. -->

<xsl:template match="/">
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Sitemap · Cheewawa</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700;800&amp;family=Nunito:wght@400;600;700&amp;display=swap"/>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Nunito', Arial, sans-serif; background: #FFF7F0; color: #4A3728; padding: 40px 20px; }
        .wrap { max-width: 960px; margin: 0 auto; }
        .head { display: flex; align-items: center; gap: 14px; margin-bottom: 8px; }
        .paw { width: 44px; height: 44px; flex-shrink: 0; }
        h1 { font-family: 'Baloo 2', Arial, sans-serif; font-size: 2rem; color: #B4652A; }
        .sub { color: #8A6D57; margin-bottom: 28px; }
        .sub b { color: #B4652A; }
        .card { background: #fff; border-radius: 18px; box-shadow: 0 6px 24px rgba(180,101,42,.08); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { font-family: 'Baloo 2', Arial, sans-serif; text-align: left; font-size: .85rem; letter-spacing: .04em; text-transform: uppercase; color: #B4652A; background: #FBE3D0; padding: 14px 18px; }
        td { padding: 13px 18px; border-top: 1px solid #F6E7DB; font-size: .95rem; vertical-align: top; }
        tr:hover td { background: #FFF9F4; }
        a { color: #4A3728; text-decoration: none; font-weight: 600; }
        a:hover { color: #B4652A; text-decoration: underline; }
        .title a { text-transform: capitalize; }
        .url a { color: #A78B72; font-size: .82rem; font-weight: 400; word-break: break-all; }
        .url a:hover { color: #B4652A; }
        .mod { color: #8A6D57; white-space: nowrap; }
        .pill { display: inline-block; background: #EFE7DC; color: #7C5B46; border-radius: 999px; padding: 2px 12px; font-size: .78rem; font-weight: 700; white-space: nowrap; }
        .pill.daily { background: #FFDDE4; color: #C2455F; }
        .pill.weekly { background: #D6ECF5; color: #2E7797; }
        .pill.monthly { background: #EFE7DC; color: #7C5B46; }
        .pill.article { background: #FFDDE4; color: #C2455F; }
        .pill.category { background: #D6ECF5; color: #2E7797; }
        .pill.page { background: #F3E3CE; color: #8A4A17; }
        .pill.author { background: #E8E0F5; color: #6B4FA0; }
        .pill.home, .pill.blog { background: #DDF2E0; color: #2E7D4F; }
        .foot { margin-top: 22px; color: #A78B72; font-size: .85rem; }
        .foot a { color: #B4652A; }
        @media (max-width: 640px) {
            .url, .mod { display: none; }
            th:nth-child(2), th:nth-child(4) { display: none; }
            td, th { padding: 11px 12px; }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="head">
            <svg class="paw" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                <g fill="#D79A55" transform="translate(-4.9 -2.5) scale(0.78)">
                    <path d="M43 48c13 0 25 12 25 24 0 8-6 12-14 12-4 0-7-2-11-2s-7 2-11 2c-8 0-14-4-14-12 0-12 12-24 25-24Z"/>
                    <ellipse cx="22" cy="33" rx="10" ry="14" transform="rotate(-18 22 33)"/>
                    <ellipse cx="43" cy="22" rx="10" ry="14"/>
                    <ellipse cx="64" cy="33" rx="10" ry="14" transform="rotate(18 64 33)"/>
                </g>
                <path d="M198 25c0-6 9-6 9 0 0-6 9-6 9 0 0 8-9 14-9 14s-9-6-9-14Z" fill="#F86F86" transform="translate(-120.6 -16.4) scale(0.85)"/>
            </svg>
            <h1>Cheewawa Sitemap</h1>
        </div>
        <p class="sub">Every page on the site, <b><xsl:value-of select="count(sm:urlset/sm:url)"/></b> in total.
           This is the friendly view; search engines read the raw XML underneath.</p>
        <div class="card">
            <table>
                <tr><th>Title</th><th>URL</th><th>Type</th><th>Last updated</th><th>Crawl hint</th></tr>
                <xsl:for-each select="sm:urlset/sm:url">
                    <xsl:variable name="path" select="substring-after(substring-after(sm:loc, '://'), '/')"/>
                    <xsl:variable name="slug">
                        <xsl:choose>
                            <xsl:when test="contains($path, '/')"><xsl:value-of select="substring-after($path, '/')"/></xsl:when>
                            <xsl:otherwise><xsl:value-of select="$path"/></xsl:otherwise>
                        </xsl:choose>
                    </xsl:variable>
                    <xsl:variable name="type">
                        <xsl:choose>
                            <xsl:when test="$path = ''">Home</xsl:when>
                            <xsl:when test="starts-with($path, 'post/')">Article</xsl:when>
                            <xsl:when test="starts-with($path, 'category/')">Category</xsl:when>
                            <xsl:when test="starts-with($path, 'author/')">Author</xsl:when>
                            <xsl:when test="$path = 'blog'">Blog</xsl:when>
                            <xsl:otherwise>Page</xsl:otherwise>
                        </xsl:choose>
                    </xsl:variable>
                    <tr>
                        <td class="title">
                            <a href="{sm:loc}">
                                <xsl:choose>
                                    <xsl:when test="$path = ''">Home</xsl:when>
                                    <xsl:otherwise><xsl:value-of select="translate($slug, '-', ' ')"/></xsl:otherwise>
                                </xsl:choose>
                            </a>
                        </td>
                        <td class="url">
                            <a href="{sm:loc}">/<xsl:value-of select="$path"/></a>
                        </td>
                        <td>
                            <span class="pill {translate($type, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz')}">
                                <xsl:value-of select="$type"/>
                            </span>
                        </td>
                        <td class="mod"><xsl:value-of select="sm:lastmod"/></td>
                        <td>
                            <xsl:if test="sm:changefreq != ''">
                                <span class="pill {sm:changefreq}"><xsl:value-of select="sm:changefreq"/></span>
                            </xsl:if>
                        </td>
                    </tr>
                </xsl:for-each>
            </table>
        </div>
        <p class="foot">Generated live from the Cheewawa database · <a href="/">back to the site</a></p>
    </div>
</body>
</html>
</xsl:template>
</xsl:stylesheet>
