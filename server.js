const express = require('express');
const mysql = require('mysql2');
const path = require('path');
const cors = require('cors');
const bcrypt = require('bcrypt');
const session = require('express-session');
const nodemailer = require('nodemailer');
const multer = require('multer');

// --- Bloco de configuração do Multer (NOVO LOCAL) ---
const storage = multer.diskStorage({
    destination: function (req, file, cb) {
        cb(null, 'public/uploads/') // A pasta onde os arquivos serão salvos
    },
    filename: function (req, file, cb) {
        cb(null, Date.now() + '-' + file.originalname);
    }
});

const upload = multer({ storage: storage });
// ----------------------------------------------------

// Configuração do Nodemailer com as credenciais da Localweb
const transporter = nodemailer.createTransport({
    host: 'email-ssl.com.br',
    port: 465,
    secure: true, // Use 'true' para a porta 465 com SSL/TLS
    auth: {
        user: 'contato@imovelcwbpodcast.com.br',
        pass: 'AXXE645802#pod'
    }
});

const app = express();
const port = process.env.PORT || 3000;

// Configuração do EJS
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

app.use(session({
    secret: 'sua_chave_secreta_aqui',
    resave: false,
    saveUninitialized: true
}));

app.use(cors());

// Configuração do banco de dados
const db = mysql.createConnection({
    host: 'mysql.imovelcwbpodcast.com.br', // Ex: 'mysql.seudominio.com.br'
    user: 'BDnewslatter',      // Ex: 'db_imovelcwb'
    password: 'IMOVELcwb#64',    // A senha que a Localweb gerou
    database: 'newsletter_db'
});

// Conectar ao banco de dados
db.connect(err => {
    if (err) {
        console.error('Erro ao conectar ao banco de dados:', err);
        return;
    }
    console.log('Conectado ao banco de dados MySQL!');
});

// Middlewares
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static(path.join(__dirname, 'public')));

// Rota para a inscrição na newsletter
// Rota para a inscrição na newsletter
app.post('/subscribe', (req, res) => {
  const { email } = req.body;
  const adminEmail = 'contato@imovelcwbpodcast.com.br'; // <-- Seu e-mail da Localweb

  if (!email) {
    return res.status(400).json({ message: 'O email é obrigatório.' });
  }

  const sql = 'INSERT INTO subscribers (email) VALUES (?)';
  db.query(sql, [email], (err, result) => {
    if (err) {
      if (err.code === 'ER_DUP_ENTRY') {
        return res.status(409).json({ message: 'Este email já está cadastrado.' });
      }
      console.error('Erro ao inserir o email:', err);
      return res.status(500).json({ message: 'Erro no servidor.' });
    }

    console.log(`Novo assinante cadastrado: ${email}`);

    // --- NOVO CÓDIGO PARA ENVIAR NOTIFICAÇÃO ---
    const mailOptions = {
      from: adminEmail,
      to: adminEmail,
      subject: 'Novo assinante na newsletter!',
      html: `<p>Um novo assinante se cadastrou na newsletter:</p><strong>Email:</strong> ${email}`
    };

    transporter.sendMail(mailOptions, (error, info) => {
      if (error) {
        console.error('Erro ao enviar notificação de novo assinante:', error);
      } else {
        console.log('Notificação de novo assinante enviada para o administrador.');
      }
    });
    // ------------------------------------------

    res.status(201).json({ message: 'Email cadastrado com sucesso!' });
  });
});

// Rota para exibir o formulário de login
app.get('/admin/login', (req, res) => {
    res.render('login', { message: '' });
});

// Rota para processar o login
app.post('/admin/login', (req, res) => {
    const { username, password } = req.body;

    const sql = 'SELECT * FROM admins WHERE username = ?';
    db.query(sql, [username], (err, results) => {
        if (err) {
            console.error('Erro na consulta do banco de dados:', err);
            return res.status(500).render('login', { message: 'Erro no servidor.' });
        }

        if (results.length === 0) {
            return res.render('login', { message: 'Usuário ou senha incorretos.' });
        }

        const admin = results[0];

        bcrypt.compare(password, admin.password, (err, isMatch) => {
            if (err) {
                console.error('Erro na comparação de senhas:', err);
                return res.status(500).render('login', { message: 'Erro no servidor.' });
            }

            if (isMatch) {
                req.session.username = admin.username;
                res.redirect('/admin/dashboard');
            } else {
                res.render('login', { message: 'Usuário ou senha incorretos.' });
            }
        });
    });
});

// Rota para o painel de administração
app.get('/admin/dashboard', (req, res) => {
    if (!req.session.username) {
        return res.redirect('/admin/login');
    }

    db.query('SELECT * FROM subscribers', (err, subscribers) => {
        if (err) {
            console.error('Erro ao buscar assinantes:', err);
            return res.status(500).send('Erro no servidor.');
        }

        res.render('dashboard', { username: req.session.username, subscribers: subscribers });
    });
});

// Rota para enviar a newsletter
app.post('/admin/send-newsletter', upload.single('attachment'), (req, res) => {
    if (!req.session.username) {
        return res.redirect('/admin/login');
    }

    const { subject, content } = req.body;
    const attachment = req.file;

    let attachments = [];
    if (attachment) {
        attachments.push({
            filename: attachment.filename,
            path: attachment.path
        });
    }

    db.query('SELECT email FROM subscribers', (err, subscribers) => {
        if (err) {
            console.error('Erro ao buscar assinantes:', err);
            return res.status(500).send('Erro no servidor.');
        }

        const emails = subscribers.map(s => s.email);

        const mailOptions = {
            from: 'contato@imovelcwbpodcast.com.br',
            bcc: emails.join(','),
            subject: subject,
            html: content,
            attachments: attachments
        };

        transporter.sendMail(mailOptions, (error, info) => {
            if (error) {
                console.error('Erro ao enviar newsletter:', error);
                return res.render('dashboard', { username: req.session.username, subscribers: subscribers, message: 'Erro ao enviar a newsletter.' });
            }
            console.log('Newsletter enviada com sucesso!');
            console.log('Detalhes do envio:', info);
            res.render('dashboard', { username: req.session.username, subscribers: subscribers, message: 'Newsletter enviada com sucesso!' });
        });
    });
});


app.listen(port, () => {
    console.log(`Servidor rodando em http://localhost:${port}`);
});