import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import {CoreRESTClient} from "../../../../../RESTClient";
import PvToast from "../../../../../../../index.ci.php/public/js/components/primevue/toast/toast.esm.min.js";
import PvAutoComplete from "../../../../../../../index.ci.php/public/js/components/primevue/autocomplete/autocomplete.esm.min.js";

var editIcon = function(cell, formatterParams){ //plain text value
	return "<i class='fa fa-edit'></i>";
};
var deleteIcon = function(cell, formatterParams){ //plain text value
	return "<i class='fa fa-remove'></i>";
};

export default{
	components: {
		CoreFilterCmpt,
		PvAutoComplete,
		PvToast
	},
	props: {
		uid: String
	},
	emits: [
		'update:selected'
	],
	data() {
		return{
			tabulatorOptions: {
				ajaxURL: CoreRESTClient._generateRouterURI('components/stv/Kontakt/getKontakte/' + this.uid),
				columns:[
					{title:"Typ", field:"kontakttyp"}, //TODO(manu) mix ok?
					{title:"Kontakt", field:"kontakt"},
					{title:"Zustellung", field:"zustellung",
						formatter: (cell, formatterParams, onRendered) => {
							let output = cell.getValue() ? "ja" : "nein";
							return output;}
					},
					{title:"Anmerkung", field:"anmerkung"},
					{title:"Firma", field:"kurzbz", visible:false},
					{title:"Firma_id", field:"firma_id", visible:false},
					{title:"Person_id", field:"person_id", visible:false},
					{title:"Kontakt_id", field:"kontakt_id", visible:false},
					{title:"Standort_id", field:"standort_id", visible:false},
					{title:"letzte Änderung", field:"updateamum", visible:false},
					{title: "Actions",
						columns:[
							{formatter:editIcon, cellClick: (e, cell) => {
									this.actionEditContact(cell.getData().kontakt_id);
									console.log(cell.getRow().getIndex(), cell.getData(), this);
								}, width:50, headerSort:false, headerVisible:false},
							{formatter:deleteIcon, cellClick: (e, cell) => {
									this.actionDeleteContact(cell.getData().kontakt_id);
									console.log(cell.getRow().getIndex(), cell.getData(), this);
								}, width:50, headerSort:false, headerVisible:false },
						],
					},
				],
				layout: 'fitDataFill',
				layoutColumnsOnNewData:	false,
				height:	'auto',
				selectable:	true,
				index: 'kontakt_id'
			},
			tabulatorEvents: [
			],
			lastSelected: null,
			contactData: {
				zustellung: true,
				kontakttyp: 'email'
			},
			initData: {
				zustellung: true,
				kontakttyp: 'email'
			},
			kontakttypen: [],
			standorte: [],
			selectedStandort: null,
			filteredStandorte: null
		}
	},
	methods:{
		actionNewContact(){
			console.log("Neuen Kontakt anlegen");
			bootstrap.Modal.getOrCreateInstance(this.$refs.newContactModal).show();
		},
		actionEditContact(contact_id){
			console.log("Edit Contact mit contact_id " + contact_id);
			this.loadContact(contact_id);
			bootstrap.Modal.getOrCreateInstance(this.$refs.editContactModal).show();

		},
		actionDeleteContact(contact_id){
			console.log("Delete Contact " + contact_id);
			this.loadContact(contact_id);
			bootstrap.Modal.getOrCreateInstance(this.$refs.deleteContactModal).show();
		},
		addNewContact(formData) {
			CoreRESTClient.post('components/stv/Kontakt/addNewContact/' + this.uid,
				this.contactData
			).then(response => {
				console.log(response);
				if (!response.data.error) {
					this.$fhcAlert.alertSuccess('Speichern erfolgreich');
					this.hideModal("newContactModal");
					this.resetModal();
				} else {
					const errorData = response.data.retval;
					Object.entries(errorData).forEach(entry => {
						const [key, value] = entry;
						console.log(key, value);
						this.$fhcAlert.alertError(value);
					});
				}
			}).catch(error => {
				console.log(error);
				this.statusCode = 0;
				this.statusMsg = 'Error in Catch';
				console.log('Speichern nicht erfolgreich ' + this.statusMsg);
				this.$fhcAlert.alertError('Fehler bei Speicherroutine aufgetreten');
			}).finally(() => {
				window.scrollTo(0, 0);
				this.reload();
			});
		},
		loadContact(contact_id){
			return CoreRESTClient.get('components/stv/Kontakt/loadContact/' + contact_id
			).then(
				result => {
					console.log(this.contactData, result);
					if(result.data.retval)
						this.contactData = result.data.retval;
					else
					{
						this.contactData = {};
						this.$fhcAlert.alertError('Kein Kontakt mit Id ' + contact_id + ' gefunden');
					}

					return result;
				}
			);
		},
		deleteContact(kontakt_id){
			CoreRESTClient.post('components/stv/Kontakt/deleteContact/' + kontakt_id)
				.then(response => {
					console.log(response);
					if (!response.data.error) {
						this.statusCode = 0;
						this.statusMsg = 'success';
						console.log('Löschen erfolgreich: ' + this.statusMsg);
						this.$fhcAlert.alertSuccess('Löschen erfolgreich');
					} else {
						this.statusCode = 0;
						this.statusMsg = 'Error';
						console.log('Löschen nicht erfolgreich: ' + this.statusMsg);
						this.$fhcAlert.alertError('Keine Adresse mit Id ' + kontakt_id + ' gefunden');
					}
				}).catch(error => {
				console.log(error);
				this.statusCode = 0;
				this.statusMsg = 'Error in Catch';
				console.log('Löschen nicht erfolgreich ' + this.statusMsg);
				this.$fhcAlert.alertError('Fehler bei Löschroutine aufgetreten');
			}).finally(()=> {
				window.scrollTo(0, 0);
				this.hideModal('deleteContactModal');
				this.reload();
			});
		},
		updateContact(kontakt_id){
			CoreRESTClient.post('components/stv/Kontakt/updateContact/' + kontakt_id,
				this.contactData
			).then(response => {
				console.log(response);
				if (!response.data.error) {
					this.statusCode = 0;
					this.statusMsg = 'success';
					console.log('Speichern erfolgreich: ' + this.statusMsg);
					this.$fhcAlert.alertSuccess('Speichern erfolgreich');
					this.hideModal('editContactModal');
					this.resetModal();
					this.reload();
				} else {
					const errorData = response.data.retval;
					Object.entries(errorData).forEach(entry => {
						const [key, value] = entry;
						console.log(key, value);
						this.$fhcAlert.alertError(value);
					});
				}
			}).catch(error => {
				console.log(error);
				this.statusCode = 0;
				this.statusMsg = 'Error in Catch';
				console.log('Speichern nicht erfolgreich ' + this.statusMsg);
				this.$fhcAlert.alertError('Fehler bei Speicherroutine aufgetreten');
			}).finally(() => {
				window.scrollTo(0, 0);
				//hideModal();
				this.reload();
			});
		},
		hideModal(modalRef){
			bootstrap.Modal.getOrCreateInstance(this.$refs[modalRef]).hide();
		},
		reload(){
			this.$refs.table.reloadTable();
		},
		search(event) {
			return CoreRESTClient
				.get('components/stv/Kontakt/getStandorte/' + event.query)
				.then(result => {
					this.filteredStandorte = CoreRESTClient.getData(result.data);
				});
		},
		resetModal(){
			this.contactData = {};
			this.contactData = this.initData;
		},
	},
	created(){
		CoreRESTClient
			.get('components/stv/Kontakt/getKontakttypen')
			.then(result => {
				this.kontakttypen = result.data;
			})
			.catch(err => {
				console.error(err.response.data || err.message);
			});
	},
	template: `	
		<div class="stv-list h-100 pt-3">
		
		<!--Modal: new Contact-->
			<div ref="newContactModal" class="modal fade" id="newAContactModal" tabindex="-1" aria-labelledby="newAContactModalLabel" aria-hidden="true">
			  <div class="modal-dialog">
				<div class="modal-content">
				  <div class="modal-header">
					<h5 class="modal-title" id="newContactModalLabel">Details</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				  </div>
				  <div class="modal-body">
					<form  ref="contactData">						
						<div class="row mb-3">
							<label for="kontakttyp" class="form-label col-sm-4">Typ</label>
							<div class="col-sm-5">
								<select id="kontakttyp" class="form-control" v-model="contactData.kontakttyp">
									<option value="">-- keine Auswahl --</option>
									<option v-for="typ in kontakttypen" :key="typ.kontakttyp_kurzbz" :value="typ.kontakttyp" >{{typ.kontakttyp}}</option>
								</select>
							</div>
						</div>
						<div class="row mb-3">											   
							<label for="kontakt" class="form-label col-sm-4">Kontakt</label>
							<div class="col-sm-3">
								<input type="text" :readonly="readonly" class="form-control-sm" id="kontakt" v-model="contactData['kontakt']">
							</div>
						</div>	
						<div class="row mb-3">											   
							<label for="anmerkung" class="form-label col-sm-4">Anmerkung</label>
							<div class="col-sm-3">
								<input type="text" :readonly="readonly" class="form-control-sm" id="anmerkung" v-model="contactData['anmerkung']">
							</div>
						</div>	
						
						<div class="row mb-3">
							<label for="zustellung" class="form-label col-sm-4">Zustellung</label>
							<div class="col-sm-3 align-self-center">
								<div class="form-check">	
									<input id="zustellung" type="checkbox" class="form-check-input" value="1" v-model="contactData['zustellung']">
								</div>
							</div>
						</div>	
							
						<div class="row mb-3">
							<label for="firma_name" class="form-label col-sm-4">Firma / Standort</label>
								<div class="col-sm-3">
									<PvAutoComplete v-model="contactData['standort']" optionLabel="kurzbz" :suggestions="filteredStandorte" @complete="search" minLength="3"/>
								</div>	
						</div>		
																						   
					</form>  
								
				  </div>
				  <div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
					<button type="button" class="btn btn-primary" @click="addNewContact()">OK</button>
				  </div>
				</div>
			  </div>
			</div>
				
			<!--Modal: Edit Contact-->
			<div ref="editContactModal" class="modal fade" id="editContactModal" tabindex="-1" aria-labelledby="editContactModalLabel" aria-hidden="true">
			  <div class="modal-dialog">
				<div class="modal-content">
				  <div class="modal-header">
					<h5 class="modal-title" id="editContactModalLabel">Kontakt bearbeiten</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click="resetModal"></button>
				  </div>
				  <div class="modal-body">
						<form ref="contactData">
													
							<div class="row mb-3">
								<label for="kontakttyp" class="form-label col-sm-4">Typ</label>
								<div class="col-sm-5">
									<select id="kontakttyp" class="form-control" v-model="contactData.kontakttyp">
										<option value="">-- keine Auswahl --</option>
										<option v-for="typ in kontakttypen" :key="typ.kontakttyp_kurzbz" :value="typ.kontakttyp" >{{typ.kontakttyp}}</option>
									</select>
								</div>
							</div>
							<div class="row mb-3">											   
								<label for="kontakt" class="form-label col-sm-4">Kontakt</label>
								<div class="col-sm-3">
									<input type="text" :readonly="readonly" class="form-control-sm" id="kontakt" v-model="contactData['kontakt']">
								</div>
							</div>	
							<div class="row mb-3">											   
								<label for="anmerkung" class="form-label col-sm-4">Anmerkung</label>
								<div class="col-sm-3">
									<input type="text" :readonly="readonly" class="form-control-sm" id="anmerkung" v-model="contactData['anmerkung']">
								</div>
							</div>	
							
							<div class="row mb-3">
								<label for="zustellung" class="form-label col-sm-4">Zustellung</label>
								<div class="col-sm-3 align-self-center">
									<div class="form-check">	
										<input id="zustellung" type="checkbox" class="form-check-input" value="1" v-model="contactData['zustellung']">
									</div>
								</div>
							</div>	
							
							<div class="row mb-3">					
								<input type="hidden" :readonly="readonly" class="form-control-sm" id="standort_id" v-model="contactData.standort_id">
							</div>
							
<!--							<div class="row mb-3">											   
								<label for="name" class="form-label col-sm-4">Firma/Standort</label>
								<div class="col-sm-2">
									<input type="text" :readonly="readonly" class="form-control-sm" id="name" v-model="addressData['name']">
								</div>
							</div>	-->
							
							<div class="row mb-3">
								<label for="standort" class="form-label col-sm-4">Firma / Standort</label>
									<div v-if="contactData.kurzbz" class="col-sm-3">
										<input type="text" :readonly="readonly" class="form-control-sm" id="name" v-model="contactData.kurzbz">
									</div>					
									<div v-else class="col-sm-3">
										<PvAutoComplete v-model="contactData['standort']" optionLabel="kurzbz" :suggestions="filteredStandorte" @complete="search" minLength="3"/>
									</div>	
							</div>																					   
					</form>  
								
				  </div>
				  <div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="resetModal">Abbrechen</button>
					<button ref="Close" type="button" class="btn btn-primary" @click="updateContact(contactData.kontakt_id)">OK</button>
	
				  </div>
				</div>
			  </div>
			</div>
				
			<!--		Modal: Delete Contact-->
			<div ref="deleteContactModal" class="modal fade" id="deleteContactModal" tabindex="-1" aria-labelledby="deleteContactModalLabel" aria-hidden="true">
			  <div class="modal-dialog">
				<div class="modal-content">
				  <div class="modal-header">
					<h5 class="modal-title" id="deleteContactModalLabel">Kontakt löschen</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				  </div>
				  <div class="modal-body">	  
					<p>Kontakt {{contactData.kontakt_id}} wirklich löschen?</p>											
				  </div>
				  <div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
					<button type="button" class="btn btn-primary" @click="deleteContact(contactData.kontakt_id)">OK</button>
				  </div>
				</div>
			  </div>
			</div>
				
			<core-filter-cmpt
				ref="table"
				:tabulator-options="tabulatorOptions"
				:tabulator-events="tabulatorEvents"
				table-only
				:side-menu="false"
				reload
				new-btn-show
				new-btn-label="Neu"
				@click:new="actionNewContact"
			>
		</core-filter-cmpt>
		</div>`
};

