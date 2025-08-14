const bcrypt = require('bcrypt');
const mysql = require('mysql2');

const db = mysql.createConnection({
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'newsletter_db'
});

const username = 'admin'; // Defina o nome de usuário que você quiser
const password = 'suasenha123'; // Defina a senha que você quer usar

bcrypt.hash(password, 10, (err, hash) => {
    if (err) {
        console.error('Erro ao gerar o hash da senha:', err);
        db.end();
        return;
    }

    const sql = 'INSERT INTO admins (username, password) VALUES (?, ?)';
    db.query(sql, [username, hash], (err, result) => {
        if (err) {
            console.error('Erro ao inserir o administrador:', err);
        } else {
            console.log(`Administrador '${username}' inserido com sucesso!`);
        }
        db.end(); // Fecha a conexão com o banco de dados
    });
});