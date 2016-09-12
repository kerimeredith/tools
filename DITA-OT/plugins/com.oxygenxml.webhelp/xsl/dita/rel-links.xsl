<?xml version="1.0" encoding="UTF-8" ?>
<!--
    
Oxygen Webhelp plugin
Copyright (c) 1998-2014 Syncro Soft SRL, Romania.  All rights reserved.
Licensed under the terms stated in the license file EULA_Webhelp.txt 
available in the base directory of this Oxygen Webhelp plugin.

-->

<xsl:stylesheet version="2.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
  xmlns:ditamsg="http://dita-ot.sourceforge.net/ns/200704/ditamsg"
  xmlns:related-links="http://dita-ot.sourceforge.net/ns/200709/related-links"
  xmlns:relpath="http://dita2indesign/functions/relpath"
  exclude-result-prefixes="related-links ditamsg relpath">
  
  <xsl:import href="original/relpath_util.xsl"/>
  
  <!--main template for setting up all links after the body - applied to the related-links container-->
  <xsl:template match="*[contains(@class,' topic/related-links ')]" name="topic.related-links">
   <div>
      <xsl:call-template name="commonattributes"/>
    
      <xsl:call-template name="ul-child-links"/><!--handle child/descendants outside of linklists in collection-type=unordered or choice-->
    
      <xsl:call-template name="ol-child-links"/><!--handle child/descendants outside of linklists in collection-type=ordered/sequence-->
    
       <!-- OXYGEN PATCH START EXM-17960 - omit links generated by DITA-OT. -->
       <!-- 
         <xsl:call-template name="next-prev-parent-links"/>--><!--handle next and previous links-->
       <!-- OXYGEN PATCH END EXM-17960 - omit links generated by DITA-OT. -->
     
     <xsl:apply-templates select="." mode="related-links:group-unordered-links">
         <xsl:with-param name="nodes" select="descendant::*[contains(@class, ' topic/link ')]
           [count(. | key('omit-from-unordered-links', 1)) != count(key('omit-from-unordered-links', 1))]
           [generate-id(.)=generate-id((key('hideduplicates', concat(ancestor::*[contains(@class, ' topic/related-links ')]/parent::*[contains(@class, ' topic/topic ')]/@id, ' ',@href,@scope,@audience,@platform,@product,@otherprops,@rev,@type,normalize-space(child::*[1]))))[1])]"/>
      </xsl:apply-templates>
    
      <xsl:apply-templates select="*[contains(@class,' topic/linklist ')]"/>
     
     <xsl:text>&#160; !!!</xsl:text>
   </div>
  </xsl:template>

  <xsl:template name="makelink">
    <xsl:param name="label"/>
    <xsl:call-template name="linkdupinfo"/>
    <xsl:apply-templates select="*[contains(@class,' ditaot-d/ditaval-startprop ')]" mode="out-of-line"/>
    <xsl:apply-templates select="." mode="add-link-highlight-at-start"/>
            <a>
              <xsl:call-template name="commonattributes"/>
              <xsl:apply-templates select="." mode="add-linking-attributes"/>
              <!-- OXYGEN PATCH START EXM-17248 -->
              <!--<xsl:attribute name="onclick">parent.tocwin.expandThis(this.getAttribute('href'))</xsl:attribute>-->
              <xsl:attribute name="title">                
                <xsl:choose>
                  <xsl:when test="*[contains(@class, ' topic/linktext ')]"><xsl:apply-templates select="*[contains(@class, ' topic/linktext ')]"/></xsl:when>
                  <xsl:otherwise><!--use href--><xsl:call-template name="href"/></xsl:otherwise>
                </xsl:choose>
              </xsl:attribute>
              <xsl:if test="string-length($label) = 0">
                <xsl:attribute name="class">navheader_parent_path</xsl:attribute>
              </xsl:if>
              
              <!-- OXYGEN PATCH END EXM-17248 -->
              <xsl:apply-templates select="." mode="add-desc-as-hoverhelp"/>
              <!-- OXYGEN PATCH START EXM-17248 -->
              <xsl:choose>
                <xsl:when test="string-length($label) > 0">
                  <xsl:copy-of select="$label"/>
                  <span class="navheader_linktext">
                    <xsl:choose>
                      <xsl:when test="*[contains(@class, ' topic/linktext ')]"><xsl:apply-templates select="*[contains(@class, ' topic/linktext ')]"/></xsl:when>
                      <xsl:otherwise><!--use href--><xsl:call-template name="href"/></xsl:otherwise>
                    </xsl:choose>
                  </span>
                </xsl:when>
                <xsl:otherwise>
                  <xsl:choose>
                    <xsl:when test="*[contains(@class, ' topic/linktext ')]"><xsl:apply-templates select="*[contains(@class, ' topic/linktext ')]"/></xsl:when>
                    <xsl:otherwise><!--use href--><xsl:call-template name="href"/></xsl:otherwise>
                  </xsl:choose>
                </xsl:otherwise>
              </xsl:choose>
              <!-- OXYGEN PATCH END EXM-17248 -->
            </a>
    
            <xsl:apply-templates select="." mode="add-link-highlight-at-end"/>
    <xsl:apply-templates select="*[contains(@class,' ditaot-d/ditaval-endprop ')]" mode="out-of-line"/>
  </xsl:template>
  
<xsl:template match="*" mode="determine-final-href">
  <!-- OXYGEN PATCH START EXM-20602 -->
  <xsl:param name="final-path" tunnel="yes"/>
  <!-- OXYGEN PATCH END EXM-20602 -->
  <xsl:choose>
    <xsl:when test="normalize-space(@href)='' or not(@href)"/>
    <!-- For non-DITA formats - use the href as is -->
    <xsl:when test="(not(@format) and (@type='external' or @scope='external')) or (@format and not(@format='dita' or @format='DITA'))">
      <xsl:value-of select="relpath:encodeUri(@href)"/>
    </xsl:when>
    <!-- For DITA - process the internal href -->
    <xsl:when test="starts-with(@href,'#')">
      <xsl:call-template name="parsehref">
        <xsl:with-param name="href" select="relpath:encodeUri(@href)"/>
      </xsl:call-template>
    </xsl:when>
    <!-- It's to a DITA file - process the file name (adding the html extension)
    and process the rest of the href -->
    <!-- for local links respect dita.extname extension 
      and for peer links accept both .xml and .dita bug:3059256-->
    <xsl:when test="(not(@scope) or @scope='local' or @scope='peer') and (not(@format) or @format='dita' or @format='DITA')">
      <!-- OXYGEN PATCH START EXM-20602 -->
      <xsl:choose>
        <xsl:when test="string-length($final-path) > 0">
        <xsl:variable name="finalPathWithOutext">
          <xsl:call-template name="replace-extension">
            <xsl:with-param name="filename" select="$final-path"/>
            <xsl:with-param name="extension" select="$OUTEXT"/>
          </xsl:call-template>
        </xsl:variable>
          <xsl:value-of select="relpath:encodeUri($finalPathWithOutext)"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:call-template name="replace-extension">
            <xsl:with-param name="filename" select="@href"/>
            <xsl:with-param name="extension" select="$OUTEXT"/>
            <xsl:with-param name="ignore-fragment" select="true()"/>
          </xsl:call-template>
        </xsl:otherwise>
      </xsl:choose>
      <!-- OXYGEN PATCH END EXM-20602 -->

      <xsl:if test="contains(@href, '#')">
          <xsl:text>#</xsl:text>
          <xsl:variable name="anchor">
            <xsl:value-of select="substring-after(@href, '#')"></xsl:value-of>
          </xsl:variable>
          <xsl:call-template name="parsehref">
            <xsl:with-param name="href" select="relpath:encodeUri($anchor)"/>
          </xsl:call-template>
      </xsl:if>
    </xsl:when>
    <xsl:otherwise>
      <xsl:apply-templates select="." mode="ditamsg:unknown-extension"/>
      <xsl:value-of select="relpath:encodeUri(@href)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>
  
<!-- OXYGEN PATCH END EXM-23770 -->
<xsl:template match="*[@collection-type='sequence']/*[contains(@class, ' topic/link ')][@role='child' or @role='descendant']" priority="3" name="topic.link_orderedchild">
    <xsl:variable name="el-name">
        <xsl:choose>
            <xsl:when test="contains(../@class,' topic/linklist ')">div</xsl:when>
            <xsl:otherwise>li</xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    <xsl:element name="{$el-name}">
        <xsl:attribute name="class">olchildlink</xsl:attribute>
        <xsl:call-template name="commonattributes">
            <xsl:with-param name="default-output-class" select="'olchildlink'"/>
        </xsl:call-template>
        <!-- Allow for unknown metadata (future-proofing) -->
        <xsl:apply-templates select="*[contains(@class,' topic/data ') or contains(@class,' topic/foreign ')]"/>
        <xsl:apply-templates select="*[contains(@class,' ditaot-d/ditaval-startprop ')]" mode="out-of-line"/>
        <xsl:apply-templates select="." mode="related-links:ordered.child.prefix"/>
        <xsl:apply-templates select="." mode="add-link-highlight-at-start"/>
        <a>
            <xsl:apply-templates select="." mode="add-linking-attributes"/>
            <xsl:apply-templates select="." mode="add-hoverhelp-to-child-links"/>
            
            <!--use linktext as linktext if it exists, otherwise use href as linktext-->
            <xsl:choose>
                <xsl:when test="*[contains(@class, ' topic/linktext ')]"><xsl:apply-templates select="*[contains(@class, ' topic/linktext ')]"/></xsl:when>
                <xsl:otherwise><!--use href--><xsl:call-template name="href"/></xsl:otherwise>
            </xsl:choose>
        </a>
        <xsl:apply-templates select="." mode="add-link-highlight-at-end"/>
        <xsl:apply-templates select="*[contains(@class,' ditaot-d/ditaval-endprop ')]" mode="out-of-line"/>
        <xsl:value-of select="$newline"/>
        <!--add the description on a new line, unlike an info, to avoid issues with punctuation (adding a period)-->
        <xsl:variable name="topicDesc">
            <xsl:apply-templates select="*[contains(@class, ' topic/desc ')]"/>
        </xsl:variable>
        <xsl:if test="string-length(normalize-space($topicDesc)) > 0">
            <div><xsl:value-of select="$topicDesc"/></div>
        </xsl:if>
    </xsl:element><xsl:value-of select="$newline"/>
</xsl:template>
  
</xsl:stylesheet>