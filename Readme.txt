Bienvenido a la configuración paso a paso del Sistema de Inventario Tecnologico

1) Paso abrir el archivo "sistema_matricula.sql" y seguir todas las instrucciones esto creara la base de datos
con las tablas necesarias y la vista utilizada en el programa.

2) El proyeccto no maneja imagenes

3) Puede ser utilizado con apache nginx

4) Esta diseñado con el modelo MVC

### MONTAJE DE CONTENEDOR CON DOCKER ##
docker-compose build
docker-compose up

#dar de baja al contenedor para aplicar los cambios
docker-compose down -v  

#volver a levantar el contenedor volviendo a compilar y en segundo plano
docker-compose up --build -d