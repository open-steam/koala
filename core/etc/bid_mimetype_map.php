<?php

  /****************************************************************************
  mimetype_map.php - mapping for filename tails and mimetypes
  Copyright (C)

  This program is free software; you can redistribute it and/or modify it
  under the terms of the GNU General Public License as published by the
  Free Software Foundation; either version 2 of the License,
  or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
  See the GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software Foundation,
  Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA

  Author: Henrik Beige
  EMail: hebeige@gmx.de

  Modifications by hase, 18.05.2005:
  Added several MIME types
  ****************************************************************************/

  $mimetype_map = array(
    ".3gp" => "video/3gpp",                         //3gp
    ".ai" => "application/postscript",              //PostScript
    ".avi" => "video/x-msvideo",                    //Microsoft video
    ".bin" => "application/octet-stream",           //Binary, UUencoded
    ".bmp" => "image/x-ms-bmp",                     //Microsoft Windows bitmap
    ".c" => "text/plain",                           //Plain text: documents; program listings
    ".cc" => "text/plain",                          //Plain text: documents; program listings
    ".cdr" => "application/x-coreldraw",            //CorelDraw
    ".cpp" => "text/plain",                         //Plain text: documents; program listings
    ".css" => "text/css",                           //Cascading Stylesheets
    ".doc" => "application/msword",                 //Windows Word Documents
    ".docx" =>"application/vnd.openxmlformats-officedocument.wordprocessingml.document",    //Windows Word XML Documents
    ".emm" => "application/mjet-mm",                //Mindmanager Mindjet
    ".eps" => "application/postscript",              //PostScript
    ".exe" => "application/octet-stream",           //PC executable
    ".flv" => "video/x-flv",                        //Flash Video Format
    ".gif" => "image/gif",                          //Comupserver GIF
    ".h" => "text/plain",                           //Plain text: documents; program listings
    ".htm" => "text/html",                          //HTML text data (RFC 1866)
    ".html" => "text/html",                         //HTML text data (RFC 1866)
    ".hqx" => "application/mac-binhex40",           //Macintosh Binhexed archive
    ".jpg" => "image/jpg",                          //JPEG
    ".jpg" => "image/jpg",                          //JPEG
    ".jpe" => "image/jpg",                          //JPEG
    ".js" => "text/javascript",                     //Javascript program
    ".kml" => "application/vnd.google-earth.kml+xml",  //Google KML file
    ".kmz" => "application/vnd.google-earth.kmz",   //Google KMZ file
    ".latex" => "application/x-latex",              //LaTeX document
    ".mail" => "application/x-mailfolder",          //Mail folder
    ".mid" => "audio/x-midi",                       //MIDI music data
    ".mmp" => "application/mjet-mm",                //Mindmanager Mindjet
    ".mov" => "video/quicktime",                    //Macintosh Quicktime
    ".mp2a" => "audio/x-mpeg-2",                    //MPEG-2 audio
    ".mp2v" => "video/mpeg-2",                      //MPEG-2 video
    ".mp3" => "audio/mpeg",                         //MP3 audio
    ".mp4" => "video/mp4",                         //MPEG-4 video
    ".m4v" => "video/x-m4v",                         //MPEG-4 video
    ".mpa" => "video/mpeg",                         //MPEG audio
    ".mpa2" => "audio/x-mpeg-2",                    //MPEG-2 audio
    ".mpe" => "video/mpeg",                         //MPEG video
    ".mpeg" => "video/mpeg",                        //MPEG video
    ".mpega" => "audio/x-mpeg",                     //MPEG audio
    ".mpg" => "video/mpeg",                         //MPEG video
    ".mpv2" => "video/mpeg",                        //MPEG-2 video
    ".odp" => "application/vnd.oasis.opendocument.presentation",  //Open Document Presentation
    ".ods" => "application/vnd.oasis.opendocument.spreadsheet",  //Open Document Spreadsheet
    ".odt" => "application/vnd.oasis.opendocument.text",  //Open Document Text
    ".pdf" => "application/pdf",                    //Adobe Acrobat PDF
    ".png" => "image/png",                          //Portable Network Graphics
    ".ppt" => "application/ms-powerpoint",          //PowerPoint (Microsoft)
    ".pptx"=> "application/vnd.openxmlformats-officedocument.presentationml.presentation", //PowerPoint (Microsoft) XML
    ".pps" => "application/ms-powerpoint",          //PowerPoint (Microsoft)
    ".ppz" => "application/ms-powerpoint",          //PowerPoint presentation (Microsoft)
    ".ps" => "application/postscript",              //PostScript
    ".qt" => "video/quicktime",                     //Macintosh Quicktime
    ".ram" => "application/x-pn-audioman",          //Realaudio (Progressive Networks)
    ".rgb" => "image/rgb",                          //RGB
    ".rtf" => "application/rtf",                    //Microsoft Rich Text Format
    ".sit" => "application/x-stuffit",              //Macintosh Stuffit Archive
    ".snd" => "audio/basic",                        //"basic"audio - 8-bit u-law PCM
    ".swf" => "application/x-shockwave-flash",      //Shockwave Flash
    ".svg" => "image/svg+xml",                      //Scalable Vector Graphics
    ".sxc" => "application/vnd.sun.xml.calc",       //Staroffice XML Calc
    ".sxd" => "application/vnd.sun.xml.draw",       //Staroffice XML Calc
    ".sxi" => "application/vnd.sun.xml.impress",    //Staroffice XML Impress
    ".sxw" => "application/vnd.sun.xml.writer",     //Staroffice XML Writer
    ".tar" => "application/x-tar",                  //4.3BSD tar format
    ".tex" => "application/x-tex",                  //Tex/LaTeX document
    ".texi" => "application/x-texinfo",             //GNU TexInfo document
    ".texinfo" => "application/x-texinfo",          //GNU TexInfo document
    ".tif" => "image/tiff",                         //TIFF
    ".tiff" => "image/tiff",                        //TIFF
    ".txt" => "text/plain",                         //Plain text: documents; program listings
    ".vi" => "application/x-robolab",               //Robolab files (Lego)
    ".vsd" => "application/vnd.visio",              //Microsoft Visio
    ".vrml" => "x-world/x-vrml",                    //VRML data file
    ".wav" => "audio/x-wav",                        //Microsoft audio
    ".wmv" => "video/x-ms-wmv",                     //Microsoft Windows Media Video
    ".wrl" => "x-world/x-vrml",                     //VRML data file
    ".xls" => "application/ms-excel",               //Microsoft Excel
    ".xlsx" > "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",  //Microsoft Excel XML
    ".xlt" => "application/ms-excel",               //Microsoft Excel (Mustervorlage)
    ".xml" => "text/xml",                           //XML text data
    ".zip" => "application/zip"                     //Zipped archive
  );
?>
