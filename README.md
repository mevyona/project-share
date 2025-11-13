Setup le projet :

en root : nano /etc/apache2/conf-available/project-share.conf

dans le fichier :

Alias /project-share /var/www/html/project-share/public
<Directory /var/www/html/project-share/public>
AllowOverride All
Order Allow,Deny
Allow from All
</Directory>

activer l'alias :
a2enconf project-share.conf

systemctl reload apache2
