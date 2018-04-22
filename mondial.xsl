<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xs="http://www.w3.org/2001/XMLSchema"
    exclude-result-prefixes="xs"
    version="2.0">
    <xsl:output indent="yes"></xsl:output>
    <xsl:template match ="/">
        <EM>
            <xsl:apply-templates></xsl:apply-templates>
        </EM>
    </xsl:template>
    
    <xsl:template match ="mondial">
        <liste-pays>
            <xsl:apply-templates select="country"></xsl:apply-templates>
        </liste-pays>
        <liste-espace-maritime>
            <xsl:apply-templates select="sea"></xsl:apply-templates>
        </liste-espace-maritime>
    </xsl:template>
    
    <xsl:template match = "country" name="throught-template">
        <xsl:element name="Pays">
            <xsl:attribute name="id_pays">
                <xsl:value-of select = "./@car_code"></xsl:value-of>
            </xsl:attribute>
            <xsl:attribute name="Nom">
                <xsl:value-of select="./name/text()"></xsl:value-of>
            </xsl:attribute>
            <xsl:attribute name="Superficie">
                <xsl:value-of select = "./@area"></xsl:value-of>
            </xsl:attribute>
            <xsl:attribute name="Nb_hab">
                <xsl:value-of select = "./population[position() eq last()]/text()" ></xsl:value-of>
            </xsl:attribute>
            <xsl:apply-templates select = "../river[id(@country)/@car_code = current()/@car_code]"></xsl:apply-templates>
        </xsl:element>
    </xsl:template>
    
    <xsl:template match = "river">
        <xsl:element name="Fleuve">
            <xsl:attribute name="id_fleuve">
                <xsl:value-of select="./@id"></xsl:value-of>
            </xsl:attribute>
            <xsl:attribute name="Nom_fleuve">
                <xsl:value-of select ="./name/text()"></xsl:value-of>
            </xsl:attribute>
            <xsl:attribute name="Longueur">
                <xsl:value-of select ="./length/text()"></xsl:value-of>
            </xsl:attribute>
            <xsl:attribute name="Se_jette">
                <xsl:value-of select ="./to[@watertype = 'sea']/@id"></xsl:value-of>
            </xsl:attribute>
        
             <xsl:apply-templates select = "id(@country)" mode="Parcourt"></xsl:apply-templates>
        </xsl:element>
    </xsl:template>
    
    <xsl:template match = "country" mode="Parcourt">
        <xsl:element name="Parcourt">
            <xsl:attribute name="Pays">
                <xsl:value-of select = "./@car_code"></xsl:value-of>
            </xsl:attribute>
            <xsl:attribute name="distance">
                <xsl:value-of select ="'INCONNU'"></xsl:value-of>
            </xsl:attribute>
        </xsl:element>
    </xsl:template>

    <xsl:template match ="sea">
        <xsl:element name="espace-maritime">
            <xsl:attribute name="id_espace_maritime">
                <xsl:value-of select="./@id"></xsl:value-of>
            </xsl:attribute>
            <xsl:attribute name="nom_espace_maritime">
                <xsl:value-of select="./name/text()"></xsl:value-of>
            </xsl:attribute>
            <xsl:attribute name="type">
                <xsl:value-of select="'INCONNU'"></xsl:value-of>
            </xsl:attribute>
            <xsl:apply-templates select ="id(@country)" mode="cotoie"></xsl:apply-templates>
        </xsl:element>
       
    </xsl:template>
    
    <xsl:template match="country" mode="cotoie">
        <xsl:element name="Cotoie">
            <xsl:attribute name="id_pays">
                <xsl:value-of select ="./@car_code"></xsl:value-of>
            </xsl:attribute>
        </xsl:element>
    </xsl:template>    
</xsl:stylesheet>