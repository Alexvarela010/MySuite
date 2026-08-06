# 🔐 Instrucciones para Crear los 3 Administradores

## 📋 Credenciales de los Administradores

### Administrador 1
- **Nombre:** Carlos
- **Correo:** `admin1@apto.com`
- **Contraseña:** `Admin123!`

### Administrador 2
- **Nombre:** Andrés Diaz
- **Correo:** `admin2@apto.com`
- **Contraseña:** `Admin456!`

### Administrador 3
- **Nombre:** root
- **Correo:** `admin3@apto.com`
- **Contraseña:** `Admin789!`

---



## 🔒 Sistema de Cifrado

**Sistema utilizado:** `bcrypt` mediante `password_hash()` de PHP

- **Función:** `password_hash($password, PASSWORD_DEFAULT)`
- **Algoritmo:** bcrypt (PASSWORD_DEFAULT en PHP usa bcrypt)
- **Salt:** Generado automáticamente (único para cada hash)
- **Costo:** 10 (configuración por defecto, balance entre seguridad y rendimiento)
- **Verificación:** `password_verify($password, $hash)` en el login



## ⚠️ Importante

1. **Cambiar contraseñas después del primer login** por seguridad
2. **No compartir las credenciales** públicamente
3. Cada administrador puede iniciar sesión desde `en/login.php` con su correo y contraseña
4. Todos los administradores tienen los mismos permisos y acceso completo al sistema

