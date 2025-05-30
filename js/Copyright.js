
var year = new Date().getFullYear();
var ft = document.getElementById('foot');

var author = document.querySelector('meta[name="author"]');
var authorContent = author.getAttribute('content');


ft.innerHTML = '<p>Copyright &copy; ' + year + ' ' + authorContent + '</p>';