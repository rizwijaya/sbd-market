<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Users extends CI_Controller
{

	public function index()
	{
		$this->load->view('template/home/header');
		$this->load->view('home');
		$this->load->view('template/home/footer');
	}

	public function registering()
	{
		$this->load->helper(array('url', 'security'));
		$this->load->library(array('form_validation'));

		$this->form_validation->set_rules(
			'nama',
			'Nama',
			'trim|min_length[2]|max_length[128]|xss_clean|required'
		);
		$this->form_validation->set_rules(
			'email',
			'Email',
			'trim|valid_email|is_unique[users.email]|min_length[2]|max_length[128]|xss_clean|required',
			['is_unique' => 'This email has already registered!']
		);
		$this->form_validation->set_rules(
			'password',
			'Password',
			'trim|min_length[5]|max_length[128]|xss_clean|required'
		);

		$this->form_validation->set_rules(
			'confirm_password',
			'Confirm Password',
			'required|matches[password]'
		);

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('template/home/header');
			$this->load->view('register');
			$this->load->view('template/home/footer');
		} else {
			$nama			=	$this->input->post('nama');
			$email			=	$this->input->post('email');
			$password		=	$this->input->post('password');
			$id_grup		=	3;
			$data = array(
				'nama'	=>	$nama,
				'email'	=>	$email,
				'password'	=>	password_hash($password, PASSWORD_DEFAULT),
				'id_grup'	=>	$id_grup
			);

			$this->load->model('account');
			$this->account->insertNewUser($data);
			$this->session->set_flashdata('pesan', '<div class="alert alert-success alert-dismissible fade show" role="alert">Akun anda berhasil dibuat, Silahkan Login!. <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</button></div>');
			redirect('home/login');
		}
	}

	public function checkingLogin()
	{
		$this->load->helper(array('url', 'security'));
		$this->load->model('account');
		$this->load->library(array('form_validation'));

		$this->form_validation->set_rules(
			'email',
			'Email',
			'trim|xss_clean|required'
		);
		$this->form_validation->set_rules(
			'password',
			'Password',
			'trim|xss_clean|required'
		);

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('template/login/header');
			$this->load->view('login');
			$this->load->view('template/login/footer');
		} else {
			$email			=	$this->input->post('email');
			$password		=	$this->input->post('password');
			$users = $this->account->checklogin($email, $password);
			if ($users == FALSE) {
				$this->session->set_flashdata('pesan', '<div class="alert alert-danger alert-dismissible fade show" role="alert">Email/Password anda salah! <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</button></div>');
				$this->load->view('template/login/header');
				$this->load->view('login');
				$this->load->view('template/login/footer');
			} else {
				//inisialisasi session
				$this->session->set_userdata('id_user', $users[0]['id_user']);
				$this->session->set_userdata('nama', $users[0]['nama']);
				$this->session->set_userdata('email', $users[0]['email']);
				$this->session->set_userdata('id_grup', $users[0]['id_grup']);
				//ke halaman welcome page yang bersesuaian
				switch ($users[0]['id_grup']) {
					case 1:
						redirect('pemilik');
						break;
					case 2:
						redirect('pegawai');
						break;
					case 3:
						redirect('pelanggan');
						break;
					default:
						redirect('home');
						break;
				}
			}
		}
	}

	public function logout()
	{
		$this->session->sess_destroy();
		redirect('home');
	}
}
