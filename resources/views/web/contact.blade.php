<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
 <title>{{ $seo_title ?? 'Default Title' }}</title>
    <meta name="description" content="{{ $seo_description ?? 'Default Description' }}">
    <meta name="keywords" content="{{ $seo_keywords ?? 'keyword1, keyword2' }}">
    <meta property="og:title" content="{{ $seo_og_title ?? '' }}">
    <meta property="og:description" content="{{ $seo_og_description ?? '' }}">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&amp;family=Nunito:wght@600;700;800&amp;family=Pacifico&amp;display=swap" rel="stylesheet">
<style>
* {
font-family: "Heebo", sans-serif;
}
 
body {
margin: 0;
font-size: 16px;
}
 
.banner {
background: linear-gradient(115deg, rgba(102, 125, 255, 1) 0%, rgba(122, 140, 255, 1) 89%);
padding: 60px 0;
display: flex;
justify-content: center;
align-items: center;
color: black;
margin-bottom: 20px;
animation: slideFromTop 1s ease-out;
}
 
.banner h1 {
font-size: 80px;
color: white;
text-shadow: 0 2px 6px rgba(0, 0, 0, .459);
}
 
.container {
padding: 0 20px;
animation: slideFrombottom 1s ease-out;
max-width: 800px;
margin: auto;
}
 
@keyframes  slideFromTop {
from {
transform: translateY(-100px);
opacity: 0;
}
to {
transform: translateY(0);
opacity: 1;
}
}
 
@keyframes  slideFrombottom {
from {
transform: translateY(100px);
opacity: 0;
}
to {
transform: translateY(0);
opacity: 1;
}
}
 
@media (max-width: 991.5px) and (min-width: 400px) {
.banner h1 {
font-size: 40px;
}
 
.banner {
padding: 32px 0;
}
 
.container {
padding: 0 15px;
}
 
body {
font-size: 14px;
}
}
 
@media (max-width: 399px) {
.banner h1 {
font-size: 30px;
}
 
.banner {
padding: 30px 0;
}
 
.container {
padding: 0 7px;
}
 
body {
font-size: 14px;
}
}
 
body {
font-family: Arial, Helvetica, sans-serif;
}
 
* {
box-sizing: border-box;
}
 
input[type=text], input[type=email], select, textarea {
width: 100%;
padding: 12px;
border: 1px solid #ccc;
border-radius: 4px;
box-sizing: border-box;
margin-top: 6px;
margin-bottom: 16px;
resize: vertical;
}
 
input[type=submit] {
background-color: #121331;
color: white;
padding: 12px 20px;
border: none;
border-radius: 4px;
cursor: pointer;
}
 
input[type=submit]:hover {
background-color: #121331;
}
 
.container {
border-radius: 5px;
background-color: #f2f2f2;
padding: 20px;
display: flex;
flex-direction: column;
align-items: center;
}
 
form {
width: 100%;
display: flex;
flex-direction: column;
}
 
.form-group {
display: flex;
flex-direction: column;
width: 100%;
}
 
@media (min-width: 600px) {
.form-group {
flex-direction: row;
justify-content: space-between;
}
 
.form-group > div {
flex: 1;
margin-right: 10px;
}
 
.form-group > div:last-child {
margin-right: 0;
}
}
</style>
</head>
<body>
<div class="banner">
<h1>Contact Us</h1>
</div>
 
<h3 style="text-align: center;">Contact Form</h3>
 
<div class="container">
<form  enctype="multipart/form-data">
<input type="hidden" name="_token" value="xXUwz1tq0mdpa27upFsvsbfVKy9MASgs9eeAWxZG">        <div class="form-group">
<div>
<label for="name">Name</label>
<input type="text" class="form-control" name="name" id="name" placeholder="Your Name" required>
</div>
<div>
<label for="email">Email</label>
<input type="email" class="form-control" name="email" id="email" placeholder="Your Email" required>
</div>
</div>
 
<div>
<label for="subject">Subject</label>
<input type="text" class="form-control" name="subject" id="subject" placeholder="Subject" required>
</div>
 
<div>
<label for="message">Message</label>
<textarea class="form-control" name="message" id="message" placeholder="Leave a message here" style="height: 150px" required></textarea>
</div>
 
<input type="submit" value="Submit">
</form>
</div>
</body>
</html>