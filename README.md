# Inplayzitate
Ermöglicht den Usern Zitate aus den Inplay einzureichen, welches sie Lustig, schön oder einfach toll fanden. Der betroffende Account wird mit einem Alert informiert. <br />
Die Zitate werden in den Statistiken standardmäßig ausgelesen, kann aber über php in templates auch innerhalb des Forum ausgelesen werden, da es global geschaltet ist. <br />
Zitate der Charaktere werden im Profil ausgelesen und auf einer eigenen Seite. Dort kann man entscheiden, ob die Zitate mit Avatar/Bild ausgelesen aus Profilfeld angezeigt wird oder nicht.

## neue Templates
- inplayquotes_index 	
- inplayquotes_index_bit 	
- inplayquotes_misc 	
- inplayquotes_misc_bit 	
- inplayquotes_misc_bit_avatar 	
- inplayquotes_misc_option 	
- inplayquotes_postbit 	
- inplayquotes_profile

## neue Datenbank
inplayquotes

## pfad zu den Zitate
misc.php?action=inplayquotes

## CSS
inplayquotes.css

```
.inplayquotes_box{
	margin: 10px 20px;	
}

.inplayquotes_subject{
	text-align: center;
	font-size: 13px;
	margin: 5px auto;
	background: #0066a2 url(../../../images/thead.png) top left repeat-x;
  color: #ffffff;
  border-bottom: 1px solid #263c30;
  padding: 8px;
}

.inplayquotes_from{
	text-align: center;
	font-size: 12px;
	margin: 5px auto;
}

.inplayquotes_quote{
background: #0f0f0f url(../../../images/tcat.png) repeat-x;
  color: #fff;
  border-top: 1px solid #444;
  border-bottom: 1px solid #000;
  padding: 6px;
  font-size: 12px;
		text-align: center;
	box-sizing: border-box;
	margin: 10px auto;
}

.inplayquotes_textarea{
	margin: 10px auto;
	padding: 0 10px;
	width: 500px;
}

.inplayquotes_submit{
	text-align: center;
	margin:  auto;
}

/*inplayquotes misc*/

.inplayquotes_misc_flex{
		display: flex;
		flex-wrap: wrap;
		justify-content: start-flex;
}

.inplayquotes_misc_box{
	width: 47%;
	padding: 10px;
	box-sizing: border-box;
	border-top: 1px solid #fff;
  border-bottom: 1px solid #ccc;
	margin: 10px 20px;
}

.inplayquotes_misc_avatar{
	width: 10%;
	margin: 10px;
}

.inplayquotes_misc_avatar img{
	width: 100%;	
}

.inplayquotes_misc_avatar_box{
		width: 87%;
		margin:10px 5px;
		padding: 10px;
	box-sizing: border-box;
	border-top: 1px solid #fff;
  border-bottom: 1px solid #ccc;
}

.inplayquotes_misc_quote{
	width: 90%;
	text-align: justify;
	margin: 5px 10px;
	height: 75px;
	overflow: auto;
	padding: 2px 5px;
	box-sizing: border-box;
}
.inplayquotes_misc_info{
	font-size: 11px;
	text-align: center;
}

/*index*/

.inplayquotes_index_by{
	font-weight: bold;
	font-size: normal;
		text-align: center;
}

.inplayquotes_index_quote{
		width: 90%;
	text-align: justify;
	margin: 5px auto;
	padding: 2px 5px;
	box-sizing: border-box;
	margin: auto;
}

.inplayquotes_index_outof{
font-weight: bold;
	text-align: center;
}

.inplayquotes_index_goto{
		text-align: center;
}

.inplayquotes_index_noquote{
	font-weight: bold;
	text-align: center;
}

/*profile*/

.inplayquotes_profile{
	margin: 10px 20px;
	text-align: center;
}

.inplayquotes_profile_quote{
	font-size: 14px;
}

.inplayquotes_profile_quote::before{
	content: "»";
	font-size: 14px;
	padding: 1px 5px 1px 0;
}

.inplayquotes_profile_quote::after{
	content: "«";
	font-size: 14px;
	padding: 1px 0 1px 5px;
}

.inplayquote_profile_outof{
	font-size: 10px;
	text-transform: uppercase;
}
```

## Anpassung Spielerübersicht von Lara
Da beide Plugins die gleiche Datenbankbezeichnung nutzen, muss hier keine Anpassung vorgenommen werden
