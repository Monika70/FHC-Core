/**
 * Copyright (C) 2022 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

import FhcSearchbar from "../searchbar/searchbar.js";
import VerticalSplit from "../verticalsplit/verticalsplit.js";
import StvVerband from "./Studentenverwaltung/Verband.js";
import StvList from "./Studentenverwaltung/List.js";
import StvDetails from "./Studentenverwaltung/Details.js";
import StvStudiensemester from "./Studentenverwaltung/Studiensemester.js";
import {CoreRESTClient} from '../../RESTClient.js';


export default {
	components: {
		FhcSearchbar,
		VerticalSplit,
		StvVerband,
		StvList,
		StvDetails,
		StvStudiensemester
	},
	props: {
		defaultSemester: String,
		config: Object,
		permissions: Object,
		stvRoot: String,
		cisRoot: String,
		activeAddons: String // semicolon separated list of active addons
	},
	provide() {
		return {
			cisRoot: this.cisRoot,
			activeAddonBewerbung: this.activeAddons.split(';').includes('bewerbung'),
			configGenerateAlias: this.config.generateAlias,
			hasBpkPermission: this.permissions['student/bpk'],
			hasAliasPermission: this.permissions['student/alias'],
			lists: this.lists
		}
	},
	data() {
		return {
			selected: [],
			searchbaroptions: {
				types: [
					"student",
					"prestudent"
				],
				actions: {
					student: {
						defaultaction: {
							type: "link",
							action: function(data) { 
								return FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/studentenverwaltung/student/' + data.uid;
							}
						},
						childactions: [
						]
					},
					prestudent: {
						defaultaction: {
							type: "link",
							action: function(data) {
								return FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/studentenverwaltung/prestudent/' + data.prestudent_id;
							}
						},
						childactions: [
						]
					}
				}
			},
			studiengangKz: undefined,
			studiensemesterKurzbz: this.defaultSemester,
			lists: {
				nations: [],
				sprachen: [],
				geschlechter: []
			}
		}
	},
	methods: {
		onSelectVerband({link, studiengang_kz}) {
			this.studiengangKz = studiengang_kz;
			this.$refs.stvList.updateUrl(link);
		},
		searchfunction(searchsettings) {
			return Vue.$fhcapi.Search.search(searchsettings);  
		},
		studiensemesterChanged(v) {
			this.studiensemesterKurzbz = v;
			this.$refs.stvList.updateUrl();
			this.$refs.details.reload();
		}
	},
	created() {
		CoreRESTClient
			.get('components/stv/Address/getNations')
			.then(result => CoreRESTClient.getData(result.data) || [])
			.then(result => {
				this.lists.nations = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		CoreRESTClient
			.get('components/stv/Lists/getSprachen')
			.then(result => CoreRESTClient.getData(result.data) || [])
			.then(result => {
				this.lists.sprachen = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		CoreRESTClient
			.get('components/stv/Lists/getGeschlechter')
			.then(result => CoreRESTClient.getData(result.data) || [])
			.then(result => {
				this.lists.geschlechter = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		CoreRESTClient
			.get('components/stv/Lists/getAusbildungen')
			.then(result => CoreRESTClient.getData(result.data) || [])
			.then(result => {
				this.lists.ausbildungen = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		CoreRESTClient
			.get('components/stv/Lists/getStgs')
			.then(result => CoreRESTClient.getData(result.data) || [])
			.then(result => {
				this.lists.stgs = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		CoreRESTClient
			.get('components/stv/Lists/getOrgforms')
			.then(result => CoreRESTClient.getData(result.data) || [])
			.then(result => {
				this.lists.orgforms = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	mounted() {
		if (this.$route.params.id) {
			this.$refs.stvList.updateUrl('components/stv/students/uid/' + this.$route.params.id, true);
		} else if (this.$route.params.prestudent_id) {
			this.$refs.stvList.updateUrl('components/stv/students/prestudent/' + this.$route.params.prestudent_id, true);
		}

	},
	template: `
	<header class="navbar navbar-expand-lg navbar-dark bg-dark flex-md-nowrap p-0 shadow">
		<a class="navbar-brand col-md-4 col-lg-3 col-xl-2 me-0 px-3" :href="stvRoot">FHC 4.0</a>
		<button class="navbar-toggler d-md-none m-1 collapsed" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
		<fhc-searchbar :searchoptions="searchbaroptions" :searchfunction="searchfunction" class="searchbar w-100"></fhc-searchbar>
	</header>
	<div class="container-fluid overflow-hidden">
		<div class="row h-100">
			<nav id="sidebarMenu" class="bg-light offcanvas offcanvas-start col-md p-md-0 h-100">
				<div class="offcanvas-header justify-content-end px-1 d-md-none">
					<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
				</div>
				<stv-verband @select-verband="onSelectVerband" class="col" style="height:0%"></stv-verband>
				<stv-studiensemester :default="defaultSemester" @changed="studiensemesterChanged"></stv-studiensemester>
			</nav>
			<main class="col-md-8 ms-sm-auto col-lg-9 col-xl-10">
				<vertical-split>
					<template #top>
						<stv-list ref="stvList" v-model:selected="selected" :studiengang-kz="studiengangKz" :studiensemester-kurzbz="studiensemesterKurzbz"></stv-list>
					</template>
					<template #bottom>
						<stv-details ref="details" :students="selected"></stv-details>
					</template>
				</vertical-split>
			</main>
		</div>
	</div>`
};
