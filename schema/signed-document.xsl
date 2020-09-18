<?xml version="1.0" encoding="UTF-8" ?>
<xsl:transform version="1.0"
               xmlns="http://www.w3.org/1999/xhtml"
               xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
               xmlns:doc="https://opensource.duma.sh/xml/signed-document"
               exclude-result-prefixes="doc">
    <xsl:output method="html" indent="yes" />
    <xsl:template match="/doc:document">
        <html>
            <head>
                <title>
                    Document
                    <xsl:if test="./@id">
                        -
                        <xsl:value-of select="./@id" />
                    </xsl:if>
                </title>
            </head>
            <body>
                <h2>
                    Document
                    <xsl:if test="./@id">
                        -
                        <xsl:value-of select="./@id" />
                    </xsl:if>
                </h2>
                <xsl:call-template name="single_document">
                    <xsl:with-param name="node" select="."/>
                </xsl:call-template>
            </body>
        </html>
    </xsl:template>
    <xsl:template match="doc:documents">
        <html>
            <head>
                <title>
                    Documents Collection
                    <xsl:if test="./@id">
                        -
                        <xsl:value-of select="./@id" />
                    </xsl:if>
                </title>
            </head>
            <body>
                <h1>
                    Documents Collection
                    <xsl:if test="./@id">
                        -
                        <xsl:value-of select="./@id" />
                    </xsl:if>
                </h1>
                <ol>
                    <xsl:for-each select="./doc:document">
                        <li>
                            <a href="#{position()}">
                                Document <xsl:value-of select="position()" />
                                <xsl:if test="./@id">
                                    -
                                    <xsl:value-of select="./@id" />
                                </xsl:if>
                            </a>
                        </li>
                    </xsl:for-each>
                </ol>
                <xsl:for-each select="./doc:document">
                    <hr/>
                    <a name="{position()}"/>
                    <h2>
                        Document <xsl:value-of select="position()" />
                        <xsl:if test="./@id">
                            -
                            <xsl:value-of select="./@id" />
                        </xsl:if>
                    </h2>
                    <xsl:call-template name="single_document">
                        <xsl:with-param name="node" select="."/>
                    </xsl:call-template>
                </xsl:for-each>
            </body>
        </html>
    </xsl:template>
    <xsl:template name="default">
        <xsl:param name="node"/>
        <xsl:param name="value"/>
        <xsl:if test="$node">
            <xsl:value-of select="$node" />
        </xsl:if>
        <xsl:if test="not($node)">
            <xsl:value-of select="$value" />
        </xsl:if>
    </xsl:template>
    <xsl:template name="single_document">
        <xsl:param name="node"/>
        <p>
            ID:
            <br/>
            <strong style="font-family: 'Courier New'">
                <xsl:call-template name="default">
                    <xsl:with-param name="node" select="$node/@id"/>
                    <xsl:with-param name="value" select="'no id'"/>
                </xsl:call-template>
            </strong>
        </p>
        <p>
            Encoding:
            <br/>
            <strong style="font-family: 'Courier New'">
                <xsl:call-template name="default">
                    <xsl:with-param name="node" select="$node/doc:content/@encoding"/>
                    <xsl:with-param name="value" select="'raw'"/>
                </xsl:call-template>
            </strong>
        </p>
        <p>
            SHA256:
            <br/>
            <strong style="font-family: 'Courier New'">
                <xsl:value-of select="$node/doc:content/@sha256"/>
            </strong>
        </p>
        <pre style="border: 1px solid black; font-family: 'Courier New'"><xsl:value-of select="$node/doc:content"/></pre>
        <xsl:if test="$node/doc:signature">
            <xsl:for-each select="$node/doc:signature">
                <hr/>
                <h3>Signature</h3>
                <p>
                    ID:
                    <br/>
                    <strong style="font-family: 'Courier New'">
                        <xsl:call-template name="default">
                            <xsl:with-param name="node" select="./@id"/>
                            <xsl:with-param name="value" select="'no id'"/>
                        </xsl:call-template>
                    </strong>
                </p>
                <p>
                    Public Key:
                    <br/>
                    <strong style="font-family: 'Courier New'"><xsl:value-of select="./@public-key"/></strong>
                </p>
                <p>
                    Signature:
                    <br/>
                    <strong style="font-family: 'Courier New'"><xsl:value-of select="."/></strong>
                </p>
            </xsl:for-each>
        </xsl:if>
        <xsl:if test="not($node/doc:signature)">
            <strong style="color: #808080">No signatures attached</strong>
        </xsl:if>
    </xsl:template>
</xsl:transform>
