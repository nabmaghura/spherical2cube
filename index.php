<?php
// The cube dimension
$d=1200;
// the 360° pano
$infile="pano.jpg";
$outfile="";
$dlast=$d-1;
$dir=__DIR__;
$outpath=$dir."\\";
// get the source image width and height
list($width, $height, $type, $attr) = getimagesize($infile);
$ww=$width;
$hh=$height;
// the extension to use
$suffix=".jpg";

$im = imagecreatefromjpeg($infile);
imagesetinterpolation($im, IMG_BILINEAR_FIXED);
// Create the cube faces
for($i=0; $i<6; $i++){
$gd = imagecreatetruecolor($d, $d);

for($ii=0; $ii<$d; $ii++){
for ($j=0; $j<$d; $j++){
	
	# normalize output image coordinates to -1 to 1 relative to center with xc right and yc up
	# from pixel coords i right and j down with zero at top left corner			
$xc=((2*$ii)/($dlast)) - 1;
$yc=1 - ((2*$j)/($dlast));
	# Since spherical coordinates have theta 0 along the x axis, lets have the front face when looking along +x
	# we will use x forward, y to left and z upward as world coordinates
	# convert xc, yc to xx, yy, zz on face of cube in 3D space

switch($i){
	
case 0:
	$outfile=$outpath."vr_f".$suffix ;
	$xx=1 ; $yy=-$xc;  $zz=$yc;
	break;
case 1:
	$outfile=$outpath."vr_l".$suffix ;
	$xx=$xc ; $yy=1 ; $zz=$yc;
	break;
case 2:
	$outfile=$outpath."vr_r".$suffix ;
	$xx=$xc ; $yy=-1 ; $zz=$yc;
	break;
case 3:
	$outfile=$outpath."vr_b".$suffix ;
	$xx=-1; $yy=$xc; $zz=$yc;
	break;
case 4:
	$outfile=$outpath."vr_u".$suffix ;
	$xx=-$yc; $yy=-$xc; $zz=1;
	break;	
case 5:
	$outfile=$outpath."vr_d".$suffix ;
	$xx=$yc; $yy=-$xc; $zz=-1;
	break;
default:
	break;	
}
	
	# convert xx, yy, zz to spherical coordinates for points from the cube faces
	# note rr is not constant=1=radius of sphere; it is rr=sqrt(x^2+y^2+z^2)
	#
	# phiang is vertical on the panorama and we need the top of the image to be phiang=0 and the bottom of the image to be phiang=180 so that angles correspond to vertical image coordinate, vv
	# phiang in spherical coords does just that;  phiang is zero along z axis and increase downward to 180 along -z
	# 
	# theta is horizontal on the panorama and we need to have it 0 at the left side and 360 at the right side so that it corresponds to the horizong image coordinate, uu
	# theta in spherical coords is zero along the x axis and increasing counterclockwise towards y axis
	# so we need to make it clockwise, thus just negate the value of theta
	# but we have theta=0 in middle of image and we want theta=0 at left, so add 180 so theta=180 at the center of the image
	# 
	# see Wolfram spherical coords transformation with unswapped axes.
	# xx=rr*cos(theta)*sin(phiang)
	# yy=rr*sin(theta)*sin(phiang)
	# zz=rr*cos(phiang)
	# rr=sqrt(xx^2 + yy^2 + zz^2)
	# phiang=arccos(zz/rr)
	# theta=arctan2(yy,xx)
	
	$pi2=pi()*2;
	$pi=pi();
	$rr=sqrt(($xx*$xx) + ($yy*$yy) + ($zz*$zz));
	$phiang=acos($zz/$rr);
	$theta=$pi-atan2($yy,$xx);
	$wlast=$ww-1;
	$hlast=$hh-1;
	
	# convert theta and phi to pixels in the input image (uu,vv) at origin top left
	# theta is zero in center x
	# phiang is zero at top
	# 360 degrees corresponds to 1 pixel past the end of the image so that it wraps back to the first pixel.
	# So ww<=>360 degreesand hh<=>180 degrees
	# But to avoid a vertical bgcolor line in the back image, use last pixel (ww-1 and hh-1) to correspond to 360
	# This would be correct, if the first and last columns were the same, but the do not match perfectly.
	$uu=($theta)*($wlast/$pi2);
	$vv=($phiang)*($hlast/$pi);
	//get the color of the interpreted point
	$rgb = imagecolorat($im, round($uu), round($vv));
	$r = ($rgb >> 16) & 0xFF;
	$g = ($rgb >> 8) & 0xFF;
	$b = $rgb & 0xFF;
	//Apply the color on the output image
	$color = imagecolorallocate($gd, $r, $g, $b);
	imagesetinterpolation($gd, IMG_BILINEAR_FIXED);
	imagesetpixel($gd, $ii,$j, $color);
}
}
// save the output image
imagejpeg($gd, $outfile);

// free the memory
imagedestroy($gd);
}
?>
