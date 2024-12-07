@extends('users.layout.master')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Customers</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{route('customers')}}">Customers</a></li>
            <li class="breadcrumb-item active">Edit</li>
          </ol>
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>
  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      @if(Session::has('msg')) 
        <div class="alert alert-{{ Session::get('color') }} alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
          <h4><i class="icon {{ Session::get('icon') }}"></i> {{ Session::get('msg') }}</h4>
        </div>
      @endif
      <div class="row">
        <!-- left column -->
        <div class="col-md-12">
          <!-- general form elements -->
          <div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">Edit Customer</h3>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form role="form" action="{{ route('update-customer') }}" method="post">
              @csrf
              <div class="card-body">
                <fieldset style="border: 1px solid #ced4da; padding:0px 15px;">
                  <legend>Application & Address</legend>
                  <div class="row">
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="SelectApplicationType">Application type<span class="text-danger">*</span></label>
                          <div class="form-group clearfix">
                            <div class="icheck-primary d-inline" style="padding-right:80px;">
                              <input type="radio" name="application_type" onclick="hideSectionAccordingApplicationType(this.value)" value="P" {{ $customerArr->type=="P" ? "checked" : ""}} id="ApplicationTypePersonal">
                              <label for="ApplicationTypePersonal">Personal</label>
                            </div>
                            <div class="icheck-primary d-inline">
                              <input type="radio" name="application_type" onclick="hideSectionAccordingApplicationType(this.value)" value="S" {{ $customerArr->type=="S" ? "checked" : ""}} id="ApplicationTypeShop">
                              <label for="ApplicationTypeShop">Shop</label>
                            </div>
                          </div>
                          @error('application_type')<span class="text-danger">{{ $message }}</span>@enderror
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="SelectAddress">Full Address</label>
                        <input name="full_address" type="text" class="form-control" id="SelectAddress" disabled value="{{ buildingLocationAndUnitNumberByUnitID($customerArr->address_id) }}">
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="SelectCountry">Country<span class="text-danger">*</span></label>
                        <select name="country" class="selectpicker countrypicker form-control select2" data-flag="true" id="SelectCountry">
                          <option value="">--Select--</option>
                          <option {{ $customerArr->country=='Afganistan' ?"selected":"" }} value="Afganistan">Afghanistan</option>
                          <option {{ $customerArr->country=='Albania' ?"selected":"" }} value="Albania">Albania</option>
                          <option {{ $customerArr->country=='Algeria' ?"selected":"" }} value="Algeria">Algeria</option>
                          <option {{ $customerArr->country=='American Samoa' ?"selected":"" }} value="American Samoa">American Samoa</option>
                          <option {{ $customerArr->country=='Andorra' ?"selected":"" }} value="Andorra">Andorra</option>
                          <option {{ $customerArr->country=='Angola' ?"selected":"" }} value="Angola">Angola</option>
                          <option {{ $customerArr->country=='Anguilla' ?"selected":"" }} value="Anguilla">Anguilla</option>
                          <option {{ $customerArr->country=='Antigua & Barbuda' ?"selected":"" }} value="Antigua & Barbuda">Antigua & Barbuda</option>
                          <option {{ $customerArr->country=='Argentina' ?"selected":"" }} value="Argentina">Argentina</option>
                          <option {{ $customerArr->country=='Armenia' ?"selected":"" }} value="Armenia">Armenia</option>
                          <option {{ $customerArr->country=='Aruba' ?"selected":"" }} value="Aruba">Aruba</option>
                          <option {{ $customerArr->country=='Australia' ?"selected":"" }} value="Australia">Australia</option>
                          <option {{ $customerArr->country=='Austria' ?"selected":"" }} value="Austria">Austria</option>
                          <option {{ $customerArr->country=='Azerbaijan' ?"selected":"" }} value="Azerbaijan">Azerbaijan</option>
                          <option {{ $customerArr->country=='Bahamas' ?"selected":"" }} value="Bahamas">Bahamas</option>
                          <option {{ $customerArr->country=='Bahrain' ?"selected":"" }} value="Bahrain">Bahrain</option>
                          <option {{ $customerArr->country=='Bangladesh' ?"selected":"" }} value="Bangladesh">Bangladesh</option>
                          <option {{ $customerArr->country=='Barbados' ?"selected":"" }} value="Barbados">Barbados</option>
                          <option {{ $customerArr->country=='Belarus' ?"selected":"" }} value="Belarus">Belarus</option>
                          <option {{ $customerArr->country=='Belgium' ?"selected":"" }} value="Belgium">Belgium</option>
                          <option {{ $customerArr->country=='Belize' ?"selected":"" }} value="Belize">Belize</option>
                          <option {{ $customerArr->country=='Benin' ?"selected":"" }} value="Benin">Benin</option>
                          <option {{ $customerArr->country=='Bermuda' ?"selected":"" }} value="Bermuda">Bermuda</option>
                          <option {{ $customerArr->country=='Bhutan' ?"selected":"" }} value="Bhutan">Bhutan</option>
                          <option {{ $customerArr->country=='Bolivia' ?"selected":"" }} value="Bolivia">Bolivia</option>
                          <option {{ $customerArr->country=='Bonaire' ?"selected":"" }} value="Bonaire">Bonaire</option>
                          <option {{ $customerArr->country=='Bosnia & Herzegovina' ?"selected":"" }} value="Bosnia & Herzegovina">Bosnia & Herzegovina</option>
                          <option {{ $customerArr->country=='Botswana' ?"selected":"" }} value="Botswana">Botswana</option>
                          <option {{ $customerArr->country=='Brazil' ?"selected":"" }} value="Brazil">Brazil</option>
                          <option {{ $customerArr->country=='British Indian Ocean Territory' ?"selected":"" }} value="British Indian Ocean Territory">British Indian Ocean Territory</option>
                          <option {{ $customerArr->country=='Brunei' ?"selected":"" }} value="Brunei">Brunei</option>
                          <option {{ $customerArr->country=='Bulgaria' ?"selected":"" }} value="Bulgaria">Bulgaria</option>
                          <option {{ $customerArr->country=='Burkina Faso' ?"selected":"" }} value="Burkina Faso">Burkina Faso</option>
                          <option {{ $customerArr->country=='Burundi' ?"selected":"" }} value="Burundi">Burundi</option>
                          <option {{ $customerArr->country=='Cambodia' ?"selected":"" }} value="Cambodia">Cambodia</option>
                          <option {{ $customerArr->country=='Cameroon' ?"selected":"" }} value="Cameroon">Cameroon</option>
                          <option {{ $customerArr->country=='Canada' ?"selected":"" }} value="Canada">Canada</option>
                          <option {{ $customerArr->country=='Canary Islands' ?"selected":"" }} value="Canary Islands">Canary Islands</option>
                          <option {{ $customerArr->country=='Cape Verde' ?"selected":"" }} value="Cape Verde">Cape Verde</option>
                          <option {{ $customerArr->country=='Cayman Islands' ?"selected":"" }} value="Cayman Islands">Cayman Islands</option>
                          <option {{ $customerArr->country=='Central African Republic' ?"selected":"" }} value="Central African Republic">Central African Republic</option>
                          <option {{ $customerArr->country=='Chad' ?"selected":"" }} value="Chad">Chad</option>
                          <option {{ $customerArr->country=='Channel Islands' ?"selected":"" }} value="Channel Islands">Channel Islands</option>
                          <option {{ $customerArr->country=='Chile' ?"selected":"" }} value="Chile">Chile</option>
                          <option {{ $customerArr->country=='China' ?"selected":"" }} value="China">China</option>
                          <option {{ $customerArr->country=='Christmas Island' ?"selected":"" }} value="Christmas Island">Christmas Island</option>
                          <option {{ $customerArr->country=='Cocos Island' ?"selected":"" }} value="Cocos Island">Cocos Island</option>
                          <option {{ $customerArr->country=='Colombia' ?"selected":"" }} value="Colombia">Colombia</option>
                          <option {{ $customerArr->country=='Comoros' ?"selected":"" }} value="Comoros">Comoros</option>
                          <option {{ $customerArr->country=='Congo' ?"selected":"" }} value="Congo">Congo</option>
                          <option {{ $customerArr->country=='Cook Islands' ?"selected":"" }} value="Cook Islands">Cook Islands</option>
                          <option {{ $customerArr->country=='Costa Rica' ?"selected":"" }} value="Costa Rica">Costa Rica</option>
                          <option {{ $customerArr->country=='Cote DIvoire' ?"selected":"" }} value="Cote DIvoire">Cote DIvoire</option>
                          <option {{ $customerArr->country=='Croatia' ?"selected":"" }} value="Croatia">Croatia</option>
                          <option {{ $customerArr->country=='Cuba' ?"selected":"" }} value="Cuba">Cuba</option>
                          <option {{ $customerArr->country=='Curaco' ?"selected":"" }} value="Curaco">Curacao</option>
                          <option {{ $customerArr->country=='Cyprus' ?"selected":"" }} value="Cyprus">Cyprus</option>
                          <option {{ $customerArr->country=='Czech Republic' ?"selected":"" }} value="Czech Republic">Czech Republic</option>
                          <option {{ $customerArr->country=='Denmark' ?"selected":"" }} value="Denmark">Denmark</option>
                          <option {{ $customerArr->country=='Djibouti' ?"selected":"" }} value="Djibouti">Djibouti</option>
                          <option {{ $customerArr->country=='Dominica' ?"selected":"" }} value="Dominica">Dominica</option>
                          <option {{ $customerArr->country=='Dominican Republic' ?"selected":"" }} value="Dominican Republic">Dominican Republic</option>
                          <option {{ $customerArr->country=='East Timor' ?"selected":"" }} value="East Timor">East Timor</option>
                          <option {{ $customerArr->country=='Ecuador' ?"selected":"" }} value="Ecuador">Ecuador</option>
                          <option {{ $customerArr->country=='Egypt' ?"selected":"" }} value="Egypt">Egypt</option>
                          <option {{ $customerArr->country=='El Salvador' ?"selected":"" }} value="El Salvador">El Salvador</option>
                          <option {{ $customerArr->country=='Equatorial Guinea' ?"selected":"" }} value="Equatorial Guinea">Equatorial Guinea</option>
                          <option {{ $customerArr->country=='Eritrea' ?"selected":"" }} value="Eritrea">Eritrea</option>
                          <option {{ $customerArr->country=='Estonia' ?"selected":"" }} value="Estonia">Estonia</option>
                          <option {{ $customerArr->country=='Ethiopia' ?"selected":"" }} value="Ethiopia">Ethiopia</option>
                          <option {{ $customerArr->country=='Falkland Islands' ?"selected":"" }} value="Falkland Islands">Falkland Islands</option>
                          <option {{ $customerArr->country=='Faroe Islands' ?"selected":"" }} value="Faroe Islands">Faroe Islands</option>
                          <option {{ $customerArr->country=='Fiji' ?"selected":"" }} value="Fiji">Fiji</option>
                          <option {{ $customerArr->country=='Finland' ?"selected":"" }} value="Finland">Finland</option>
                          <option {{ $customerArr->country=='France' ?"selected":"" }} value="France">France</option>
                          <option {{ $customerArr->country=='French Guiana' ?"selected":"" }} value="French Guiana">French Guiana</option>
                          <option {{ $customerArr->country=='French Polynesia' ?"selected":"" }} value="French Polynesia">French Polynesia</option>
                          <option {{ $customerArr->country=='French Southern Territory' ?"selected":"" }} value="French Southern Territory">French Southern Territory</option>
                          <option {{ $customerArr->country=='Gabon' ?"selected":"" }} value="Gabon">Gabon</option>
                          <option {{ $customerArr->country=='Gambia' ?"selected":"" }} value="Gambia">Gambia</option>
                          <option {{ $customerArr->country=='Georgia' ?"selected":"" }} value="Georgia">Georgia</option>
                          <option {{ $customerArr->country=='Germany' ?"selected":"" }} value="Germany">Germany</option>
                          <option {{ $customerArr->country=='Ghana' ?"selected":"" }} value="Ghana">Ghana</option>
                          <option {{ $customerArr->country=='Gibraltar' ?"selected":"" }} value="Gibraltar">Gibraltar</option>
                          <option {{ $customerArr->country=='Great Britain' ?"selected":"" }} value="Great Britain">Great Britain</option>
                          <option {{ $customerArr->country=='Greece' ?"selected":"" }} value="Greece">Greece</option>
                          <option {{ $customerArr->country=='Greenland' ?"selected":"" }} value="Greenland">Greenland</option>
                          <option {{ $customerArr->country=='Grenada' ?"selected":"" }} value="Grenada">Grenada</option>
                          <option {{ $customerArr->country=='Guadeloupe' ?"selected":"" }} value="Guadeloupe">Guadeloupe</option>
                          <option {{ $customerArr->country=='Guam' ?"selected":"" }} value="Guam">Guam</option>
                          <option {{ $customerArr->country=='Guatemala' ?"selected":"" }} value="Guatemala">Guatemala</option>
                          <option {{ $customerArr->country=='Guinea' ?"selected":"" }} value="Guinea">Guinea</option>
                          <option {{ $customerArr->country=='Guyana' ?"selected":"" }} value="Guyana">Guyana</option>
                          <option {{ $customerArr->country=='Haiti' ?"selected":"" }} value="Haiti">Haiti</option>
                          <option {{ $customerArr->country=='Hawaii' ?"selected":"" }} value="Hawaii">Hawaii</option>
                          <option {{ $customerArr->country=='Honduras' ?"selected":"" }} value="Honduras">Honduras</option>
                          <option {{ $customerArr->country=='Hong Kong' ?"selected":"" }} value="Hong Kong">Hong Kong</option>
                          <option {{ $customerArr->country=='Hungary' ?"selected":"" }} value="Hungary">Hungary</option>
                          <option {{ $customerArr->country=='Iceland' ?"selected":"" }} value="Iceland">Iceland</option>
                          <option {{ $customerArr->country=='Indonesia' ?"selected":"" }} value="Indonesia">Indonesia</option>
                          <option {{ $customerArr->country=='India' ?"selected":"" }} value="India">India</option>
                          <option {{ $customerArr->country=='Iran' ?"selected":"" }} value="Iran">Iran</option>
                          <option {{ $customerArr->country=='Iraq' ?"selected":"" }} value="Iraq">Iraq</option>
                          <option {{ $customerArr->country=='Ireland' ?"selected":"" }} value="Ireland">Ireland</option>
                          <option {{ $customerArr->country=='Isle of Man' ?"selected":"" }} value="Isle of Man">Isle of Man</option>
                          <option {{ $customerArr->country=='Israel' ?"selected":"" }} value="Israel">Israel</option>
                          <option {{ $customerArr->country=='Italy' ?"selected":"" }} value="Italy">Italy</option>
                          <option {{ $customerArr->country=='Jamaica' ?"selected":"" }} value="Jamaica">Jamaica</option>
                          <option {{ $customerArr->country=='Japan' ?"selected":"" }} value="Japan">Japan</option>
                          <option {{ $customerArr->country=='Jordan' ?"selected":"" }} value="Jordan">Jordan</option>
                          <option {{ $customerArr->country=='Kazakhstan' ?"selected":"" }} value="Kazakhstan">Kazakhstan</option>
                          <option {{ $customerArr->country=='Kenya' ?"selected":"" }} value="Kenya">Kenya</option>
                          <option {{ $customerArr->country=='Kiribati' ?"selected":"" }} value="Kiribati">Kiribati</option>
                          <option {{ $customerArr->country=='North Korea' ?"selected":"" }} value="North Korea">North Korea</option>
                          <option {{ $customerArr->country=='South Korea' ?"selected":"" }} value="South Korea">South Korea</option>
                          <option {{ $customerArr->country=='Kuwait' ?"selected":"" }} value="Kuwait">Kuwait</option>
                          <option {{ $customerArr->country=='Kyrgyzstan' ?"selected":"" }} value="Kyrgyzstan">Kyrgyzstan</option>
                          <option {{ $customerArr->country=='Laos' ?"selected":"" }} value="Laos">Laos</option>
                          <option {{ $customerArr->country=='Latvia' ?"selected":"" }} value="Latvia">Latvia</option>
                          <option {{ $customerArr->country=='Lebanon' ?"selected":"" }} value="Lebanon">Lebanon</option>
                          <option {{ $customerArr->country=='Lesotho' ?"selected":"" }} value="Lesotho">Lesotho</option>
                          <option {{ $customerArr->country=='Liberia' ?"selected":"" }} value="Liberia">Liberia</option>
                          <option {{ $customerArr->country=='Libya' ?"selected":"" }} value="Libya">Libya</option>
                          <option {{ $customerArr->country=='Liechtenstein' ?"selected":"" }} value="Liechtenstein">Liechtenstein</option>
                          <option {{ $customerArr->country=='Lithuania' ?"selected":"" }} value="Lithuania">Lithuania</option>
                          <option {{ $customerArr->country=='Luxembourg' ?"selected":"" }} value="Luxembourg">Luxembourg</option>
                          <option {{ $customerArr->country=='Macau' ?"selected":"" }} value="Macau">Macau</option>
                          <option {{ $customerArr->country=='Macedonia' ?"selected":"" }} value="Macedonia">Macedonia</option>
                          <option {{ $customerArr->country=='Madagascar' ?"selected":"" }} value="Madagascar">Madagascar</option>
                          <option {{ $customerArr->country=='Malaysia' ?"selected":"" }} value="Malaysia">Malaysia</option>
                          <option {{ $customerArr->country=='Malawi' ?"selected":"" }} value="Malawi">Malawi</option>
                          <option {{ $customerArr->country=='Maldives' ?"selected":"" }} value="Maldives">Maldives</option>
                          <option {{ $customerArr->country=='Mali' ?"selected":"" }} value="Mali">Mali</option>
                          <option {{ $customerArr->country=='Malta' ?"selected":"" }} value="Malta">Malta</option>
                          <option {{ $customerArr->country=='Marshall Islands' ?"selected":"" }} value="Marshall Islands">Marshall Islands</option>
                          <option {{ $customerArr->country=='Martinique' ?"selected":"" }} value="Martinique">Martinique</option>
                          <option {{ $customerArr->country=='Mauritania' ?"selected":"" }} value="Mauritania">Mauritania</option>
                          <option {{ $customerArr->country=='Mauritius' ?"selected":"" }} value="Mauritius">Mauritius</option>
                          <option {{ $customerArr->country=='Mayotte' ?"selected":"" }} value="Mayotte">Mayotte</option>
                          <option {{ $customerArr->country=='Mexico' ?"selected":"" }} value="Mexico">Mexico</option>
                          <option {{ $customerArr->country=='Midway Islands' ?"selected":"" }} value="Midway Islands">Midway Islands</option>
                          <option {{ $customerArr->country=='Moldova' ?"selected":"" }} value="Moldova">Moldova</option>
                          <option {{ $customerArr->country=='Monaco' ?"selected":"" }} value="Monaco">Monaco</option>
                          <option {{ $customerArr->country=='Mongolia' ?"selected":"" }} value="Mongolia">Mongolia</option>
                          <option {{ $customerArr->country=='Montserrat' ?"selected":"" }} value="Montserrat">Montserrat</option>
                          <option {{ $customerArr->country=='Morocco' ?"selected":"" }} value="Morocco">Morocco</option>
                          <option {{ $customerArr->country=='Mozambique' ?"selected":"" }} value="Mozambique">Mozambique</option>
                          <option {{ $customerArr->country=='Myanmar' ?"selected":"" }} value="Myanmar">Myanmar</option>
                          <option {{ $customerArr->country=='Nambia' ?"selected":"" }} value="Nambia">Nambia</option>
                          <option {{ $customerArr->country=='Nauru' ?"selected":"" }} value="Nauru">Nauru</option>
                          <option {{ $customerArr->country=='Nepal' ?"selected":"" }} value="Nepal">Nepal</option>
                          <option {{ $customerArr->country=='Netherland Antilles' ?"selected":"" }} value="Netherland Antilles">Netherland Antilles</option>
                          <option {{ $customerArr->country=='Netherlands' ?"selected":"" }} value="Netherlands">Netherlands (Holland, Europe)</option>
                          <option {{ $customerArr->country=='Nevis' ?"selected":"" }} value="Nevis">Nevis</option>
                          <option {{ $customerArr->country=='New Caledonia' ?"selected":"" }} value="New Caledonia">New Caledonia</option>
                          <option {{ $customerArr->country=='New Zealand' ?"selected":"" }} value="New Zealand">New Zealand</option>
                          <option {{ $customerArr->country=='Nicaragua' ?"selected":"" }} value="Nicaragua">Nicaragua</option>
                          <option {{ $customerArr->country=='Niger' ?"selected":"" }} value="Niger">Niger</option>
                          <option {{ $customerArr->country=='Nigeria' ?"selected":"" }} value="Nigeria">Nigeria</option>
                          <option {{ $customerArr->country=='Niue' ?"selected":"" }} value="Niue">Niue</option>
                          <option {{ $customerArr->country=='Norfolk Island' ?"selected":"" }} value="Norfolk Island">Norfolk Island</option>
                          <option {{ $customerArr->country=='Norway' ?"selected":"" }} value="Norway">Norway</option>
                          <option {{ $customerArr->country=='Oman' ?"selected":"" }} value="Oman">Oman</option>
                          <option {{ $customerArr->country=='Pakistan' ?"selected":"" }} value="Pakistan">Pakistan</option>
                          <option {{ $customerArr->country=='Palau Island' ?"selected":"" }} value="Palau Island">Palau Island</option>
                          <option {{ $customerArr->country=='Palestine' ?"selected":"" }} value="Palestine">Palestine</option>
                          <option {{ $customerArr->country=='Panama' ?"selected":"" }} value="Panama">Panama</option>
                          <option {{ $customerArr->country=='Papua New Guinea' ?"selected":"" }} value="Papua New Guinea">Papua New Guinea</option>
                          <option {{ $customerArr->country=='Paraguay' ?"selected":"" }} value="Paraguay">Paraguay</option>
                          <option {{ $customerArr->country=='Peru' ?"selected":"" }} value="Peru">Peru</option>
                          <option {{ $customerArr->country=='Phillipines' ?"selected":"" }} value="Phillipines">Philippines</option>
                          <option {{ $customerArr->country=='Pitcairn Island' ?"selected":"" }} value="Pitcairn Island">Pitcairn Island</option>
                          <option {{ $customerArr->country=='Poland' ?"selected":"" }} value="Poland">Poland</option>
                          <option {{ $customerArr->country=='Portugal' ?"selected":"" }} value="Portugal">Portugal</option>
                          <option {{ $customerArr->country=='Puerto Rico' ?"selected":"" }} value="Puerto Rico">Puerto Rico</option>
                          <option {{ $customerArr->country=='Qatar' ?"selected":"" }} value="Qatar">Qatar</option>
                          <option {{ $customerArr->country=='Republic of Montenegro' ?"selected":"" }} value="Republic of Montenegro">Republic of Montenegro</option>
                          <option {{ $customerArr->country=='Republic of Serbia' ?"selected":"" }} value="Republic of Serbia">Republic of Serbia</option>
                          <option {{ $customerArr->country=='Reunion' ?"selected":"" }} value="Reunion">Reunion</option>
                          <option {{ $customerArr->country=='Romania' ?"selected":"" }} value="Romania">Romania</option>
                          <option {{ $customerArr->country=='Russia' ?"selected":"" }} value="Russia">Russia</option>
                          <option {{ $customerArr->country=='Rwanda' ?"selected":"" }} value="Rwanda">Rwanda</option>
                          <option {{ $customerArr->country=='St Barthelemy' ?"selected":"" }} value="St Barthelemy">St Barthelemy</option>
                          <option {{ $customerArr->country=='St Eustatius' ?"selected":"" }} value="St Eustatius">St Eustatius</option>
                          <option {{ $customerArr->country=='St Helena' ?"selected":"" }} value="St Helena">St Helena</option>
                          <option {{ $customerArr->country=='St Kitts-Nevis' ?"selected":"" }} value="St Kitts-Nevis">St Kitts-Nevis</option>
                          <option {{ $customerArr->country=='St Lucia' ?"selected":"" }} value="St Lucia">St Lucia</option>
                          <option {{ $customerArr->country=='St Maarten' ?"selected":"" }} value="St Maarten">St Maarten</option>
                          <option {{ $customerArr->country=='St Pierre & Miquelon' ?"selected":"" }} value="St Pierre & Miquelon">St Pierre & Miquelon</option>
                          <option {{ $customerArr->country=='St Vincent & Grenadines' ?"selected":"" }} value="St Vincent & Grenadines">St Vincent & Grenadines</option>
                          <option {{ $customerArr->country=='Saipan' ?"selected":"" }} value="Saipan">Saipan</option>
                          <option {{ $customerArr->country=='Samoa' ?"selected":"" }} value="Samoa">Samoa</option>
                          <option {{ $customerArr->country=='Samoa American' ?"selected":"" }} value="Samoa American">Samoa American</option>
                          <option {{ $customerArr->country=='San Marino' ?"selected":"" }} value="San Marino">San Marino</option>
                          <option {{ $customerArr->country=='Sao Tome & Principe' ?"selected":"" }} value="Sao Tome & Principe">Sao Tome & Principe</option>
                          <option {{ $customerArr->country=='Saudi Arabia' ?"selected":"" }} value="Saudi Arabia">Saudi Arabia</option>
                          <option {{ $customerArr->country=='Senegal' ?"selected":"" }} value="Senegal">Senegal</option>
                          <option {{ $customerArr->country=='Seychelles' ?"selected":"" }} value="Seychelles">Seychelles</option>
                          <option {{ $customerArr->country=='Sierra Leone' ?"selected":"" }} value="Sierra Leone">Sierra Leone</option>
                          <option {{ $customerArr->country=='Singapore' ?"selected":"" }} value="Singapore">Singapore</option>
                          <option {{ $customerArr->country=='Slovakia' ?"selected":"" }} value="Slovakia">Slovakia</option>
                          <option {{ $customerArr->country=='Slovenia' ?"selected":"" }} value="Slovenia">Slovenia</option>
                          <option {{ $customerArr->country=='Solomon Islands' ?"selected":"" }} value="Solomon Islands">Solomon Islands</option>
                          <option {{ $customerArr->country=='Somalia' ?"selected":"" }} value="Somalia">Somalia</option>
                          <option {{ $customerArr->country=='South Africa' ?"selected":"" }} value="South Africa">South Africa</option>
                          <option {{ $customerArr->country=='Spain' ?"selected":"" }} value="Spain">Spain</option>
                          <option {{ $customerArr->country=='Sri Lanka' ?"selected":"" }} value="Sri Lanka">Sri Lanka</option>
                          <option {{ $customerArr->country=='Sudan' ?"selected":"" }} value="Sudan">Sudan</option>
                          <option {{ $customerArr->country=='Suriname' ?"selected":"" }} value="Suriname">Suriname</option>
                          <option {{ $customerArr->country=='Swaziland' ?"selected":"" }} value="Swaziland">Swaziland</option>
                          <option {{ $customerArr->country=='Sweden' ?"selected":"" }} value="Sweden">Sweden</option>
                          <option {{ $customerArr->country=='Switzerland' ?"selected":"" }} value="Switzerland">Switzerland</option>
                          <option {{ $customerArr->country=='Syria' ?"selected":"" }} value="Syria">Syria</option>
                          <option {{ $customerArr->country=='Tahiti' ?"selected":"" }} value="Tahiti">Tahiti</option>
                          <option {{ $customerArr->country=='Taiwan' ?"selected":"" }} value="Taiwan">Taiwan</option>
                          <option {{ $customerArr->country=='Tajikistan' ?"selected":"" }} value="Tajikistan">Tajikistan</option>
                          <option {{ $customerArr->country=='Tanzania' ?"selected":"" }} value="Tanzania">Tanzania</option>
                          <option {{ $customerArr->country=='Thailand' ?"selected":"" }} value="Thailand">Thailand</option>
                          <option {{ $customerArr->country=='Togo' ?"selected":"" }} value="Togo">Togo</option>
                          <option {{ $customerArr->country=='Tokelau' ?"selected":"" }} value="Tokelau">Tokelau</option>
                          <option {{ $customerArr->country=='Tonga' ?"selected":"" }} value="Tonga">Tonga</option>
                          <option {{ $customerArr->country=='Trinidad & Tobago' ?"selected":"" }} value="Trinidad & Tobago">Trinidad & Tobago</option>
                          <option {{ $customerArr->country=='Tunisia' ?"selected":"" }} value="Tunisia">Tunisia</option>
                          <option {{ $customerArr->country=='Turkey' ?"selected":"" }} value="Turkey">Turkey</option>
                          <option {{ $customerArr->country=='Turkmenistan' ?"selected":"" }} value="Turkmenistan">Turkmenistan</option>
                          <option {{ $customerArr->country=='Turks & Caicos Is' ?"selected":"" }} value="Turks & Caicos Is">Turks & Caicos Is</option>
                          <option {{ $customerArr->country=='Tuvalu' ?"selected":"" }} value="Tuvalu">Tuvalu</option>
                          <option {{ $customerArr->country=='Uganda' ?"selected":"" }} value="Uganda">Uganda</option>
                          <option {{ $customerArr->country=='United Kingdom' ?"selected":"" }} value="United Kingdom">United Kingdom</option>
                          <option {{ $customerArr->country=='Ukraine' ?"selected":"" }} value="Ukraine">Ukraine</option>
                          <option {{ $customerArr->country=='United Arab Erimates' ?"selected":"" }} value="United Arab Erimates">United Arab Emirates</option>
                          <option {{ $customerArr->country=='United States of America' ?"selected":"" }} value="United States of America">United States of America</option>
                          <option {{ $customerArr->country=='Uraguay' ?"selected":"" }} value="Uraguay">Uruguay</option>
                          <option {{ $customerArr->country=='Uzbekistan' ?"selected":"" }} value="Uzbekistan">Uzbekistan</option>
                          <option {{ $customerArr->country=='Vanuatu' ?"selected":"" }} value="Vanuatu">Vanuatu</option>
                          <option {{ $customerArr->country=='Vatican City State' ?"selected":"" }} value="Vatican City State">Vatican City State</option>
                          <option {{ $customerArr->country=='Venezuela' ?"selected":"" }} value="Venezuela">Venezuela</option>
                          <option {{ $customerArr->country=='Vietnam' ?"selected":"" }} value="Vietnam">Vietnam</option>
                          <option {{ $customerArr->country=='Virgin Islands (Brit)' ?"selected":"" }} value="Virgin Islands (Brit)">Virgin Islands (Brit)</option>
                          <option {{ $customerArr->country=='Virgin Islands (USA)' ?"selected":"" }} value="Virgin Islands (USA)">Virgin Islands (USA)</option>
                          <option {{ $customerArr->country=='Wake Island' ?"selected":"" }} value="Wake Island">Wake Island</option>
                          <option {{ $customerArr->country=='Wallis & Futana Is' ?"selected":"" }} value="Wallis & Futana Is">Wallis & Futana Is</option>
                          <option {{ $customerArr->country=='Yemen' ?"selected":"" }} value="Yemen">Yemen</option>
                          <option {{ $customerArr->country=='Zaire' ?"selected":"" }} value="Zaire">Zaire</option>
                          <option {{ $customerArr->country=='Zambia' ?"selected":"" }} value="Zambia">Zambia</option>
                          <option {{ $customerArr->country=='Zimbabwe' ?"selected":"" }} value="Zimbabwe">Zimbabwe</option>
                        </select>
                        @error('country')<span class="text-danger">{{ $message }}</span>@enderror
                      </div>
                    </div>
                  </div>
                </fieldset>  
                <br/>
                <fieldset style="border: 1px solid #ced4da; padding:0px 15px;" id="PersonalApplicationSection">
                  <legend>Personal Details</legend>
                  <div class="row">
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="InputName">Customer Name @if($customerArr->type=="P")<span class="text-danger">*</span>@endif</label>
                        <input name="customer_name" type="text" class="form-control" id="InputName" placeholder="Enter customer name" value="@if($customerArr->type=="P") {{ $customerArr->name }} @endif">
                        @error('customer_name')<span class="text-danger">{{ $message }}</span>@enderror
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="InputCustomerEmail">Customer Email</label>
                        <input name="customer_email" type="text" class="form-control" id="InputCustomerEmail" placeholder="Enter customer email" value="{{ $customerArr->email }}">
                        @error('customer_email')<span class="text-danger">{{ $message }}</span>@enderror
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="InputCustomerMobile">Customer Mobile @if($customerArr->type=="P")<span class="text-danger">*</span>@endif</label>
                        <input name="customer_mobile" type="text" class="form-control" id="InputCustomerMobile" data-inputmask='"mask": "999-999-9999"' data-mask  placeholder="Enter customer mobile" value="{{ $customerArr->mobile }}">
                        @error('customer_mobile')<span class="text-danger">{{ $message }}</span>@enderror
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="SelectIsLiving">Is Living @if($customerArr->type=="P")<span class="text-danger">*</span>@endif</label>
                        <div class="form-group clearfix">
                          <div class="icheck-primary d-inline" style="padding-right:15px;">
                            <input type="radio" name="is_living" value="Y" {{ $customerArr->is_living=="Y" ? "checked" : ""}} id="IsLivingYes">
                            <label for="IsLivingYes">Yes</label>
                          </div>
                          <div class="icheck-primary d-inline">
                            <input type="radio" name="is_living" value="N" {{ $customerArr->is_living=="N" ? "checked" : ""}} id="IsLivingNo">
                            <label for="IsLivingNo">No</label>
                          </div>
                        </div>
                        @error('is_living')<span class="text-danger">{{ $message }}</span>@enderror
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="SelectSex">Sex @if(old('application_type')=="P")<span class="text-danger">*</span>@endif</label>
                        <div class="form-group clearfix">
                          <div class="icheck-primary d-inline" style="padding-right:25px;">
                            <input type="radio" name="sex" value="M" {{ $customerArr->sex=="M" ? "checked" : ""}} id="SexMale">
                            <label for="SexMale">Male</label>
                          </div>
                          <div class="icheck-primary d-inline" style="padding-right:25px;">
                            <input type="radio" name="sex" value="F" {{ $customerArr->sex=="F" ? "checked" : ""}} id="SexFemale">
                            <label for="SexFemale">Female</label>
                          </div>
                          <div class="icheck-primary d-inline">
                            <input type="radio" name="sex" value="O" {{ $customerArr->sex=="O" ? "checked" : ""}} id="SexOther">
                            <label for="SexOther">Other</label>
                          </div>
                        </div>
                        @error('sex')<span class="text-danger">{{ $message }}</span>@enderror
                      </div>
                    </div>
                  </div>
                </fieldset>
                <fieldset style="border: 1px solid #ced4da; padding:0px 15px;" id="ShopApplicationSection">
                  <legend>Shop Details</legend>
                  <div class="row">
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="InputShopName">Shop Name @if($customerArr->type=="S")<span class="text-danger">*</span>@endif</label>
                        <input name="shop_name" type="text" class="form-control" id="InputShopName" placeholder="Enter shop name" value="{{ $customerArr->shop_name }}">
                        @error('shop_name')<span class="text-danger">{{ $message }}</span>@enderror
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="InputAuthorizedPerson">Authorized Person @if($customerArr->type=="S")<span class="text-danger">*</span>@endif</label>
                        <input name="authorized_person" type="text" class="form-control" id="InputAuthorizedPerson" placeholder="Enter authorized person name" value="@if($customerArr->type=="S") {{ $customerArr->name }} @endif">
                        @error('authorized_person')<span class="text-danger">{{ $message }}</span>@enderror
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="InputShopEmail">Shop Email</label>
                        <input name="shop_email" type="text" class="form-control" id="InputShopEmail" placeholder="Enter shop email" value="{{ $customerArr->shop_email }}">
                        @error('shop_email')<span class="text-danger">{{ $message }}</span>@enderror
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="InputShopMobile">Shop Mobile @if($customerArr->type=="S")<span class="text-danger">*</span>@endif</label>
                        <input name="shop_mobile" type="text" class="form-control" id="InputShopMobile" placeholder="Enter shop mobile" data-inputmask='"mask": "999-999-9999"' data-mask value="{{ $customerArr->shop_mobile }}">
                        @error('shop_mobile')<span class="text-danger">{{ $message }}</span>@enderror
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="InputShopVatNumber">VAT Number</label>
                        <input name="shop_vat_no" type="text" class="form-control" id="InputShopVatNumber" placeholder="Enter shop vat number" value="{{ $customerArr->vat_no }}">
                        @error('shop_vat_no')<span class="text-danger">{{ $message }}</span>@enderror
                      </div>
                    </div>
                  </div>
                </fieldset>
                <br>
                <fieldset style="border: 1px solid #ced4da; padding:0px 15px;">
                  <legend>Internet Details</legend>
                  <div class="row">
                    <div class="col-md-4">
                      <div class="form-group">                        
                        <label for="InputIPAddress">IP Address<span class="text-danger">*</span></label>
                          <div class="input-group">
                            <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-laptop"></i></span>
                            </div>
                          <input name="ip_address" type="text" class="form-control" id="InputIPAddress" disabled value="{{ $customerArr->ip_address}}" data-inputmask="'alias': 'ip'" data-mask>
                          </div>
                        @error('ip_address')<span class="text-danger">{{ $message }}</span>@enderror
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="InputInternetPassword">Internet Password<span class="text-danger">*</span></label>
                          <div class="input-group">                            
                            <input name="internet_password" type="text" class="form-control" disabled id="InputInternetPassword" data-size="3" data-character-set="a-z,A-Z,0-9,#" value="{{ $customerArr->internet_password }}">
                            <div class="input-group-append generate-password">
                              <span class="input-group-text">
                                <i class="fas fa-sync-alt"></i>
                              </span>
                            </div>
                          </div>                        
                        @error('internet_password')<span class="text-danger">{{ $message }}</span>@enderror
                      </div>
                    </div>
                  </div>
                </fieldset>           
              </div>
              <!-- /.card-body -->
              <div class="card-footer">
                <input name="custID" type="hidden" value="{{ $customerArr->id }}">
                <a href="{{route('customers')}}" class="btn btn-warning"> <i class="fas fa-backward"></i> Back</a>
                <a href="{{route('edit-customer',['id'=>$customerArr->id])}}" class="btn btn-info"> <i class="fas fa-redo-alt"></i> Refresh</a>
                <button type="submit" class="btn btn-primary"> <i class="fas fa-save"></i> Save</button>
              </div>
            </form>
          </div>
          <!-- /.card -->
        </div>
        <!--/.col (left) -->
      </div>
      <!-- /.row -->
    </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<!-- InputMask -->
<script src="{{ asset('assets/plugins/inputmask/min/jquery.inputmask.bundle.min.js')}} "></script>
<script>
  $(function () {
    //Initialize Select2 Elements
    $('.select2').select2()
    //Phone
    $('[data-mask]').inputmask()
  })
  $( document ).ready(function() {
  var type=$('input[name="application_type"]:checked').val();
  hideSectionAccordingApplicationType(type);
});
function hideSectionAccordingApplicationType(type){
  if(type=='P'){
    $("#PersonalApplicationSection").show();
    $("#ShopApplicationSection").hide();
  }else if(type=='S'){
    $("#PersonalApplicationSection").hide();
    $("#ShopApplicationSection").show();
  }else{
    $("#PersonalApplicationSection").show();
    $("#ShopApplicationSection").show();
  }

}
</script>
@endsection