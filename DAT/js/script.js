// const menuBtn = document.getElementById("menu-btn");
// const mobileMenu = document.getElementById("mobile-menu");

// menuBtn.addEventListener("click", () => {
//   mobileMenu.classList.toggle("hidden");
// });

// document.querySelectorAll('input[type="radio"]').forEach((input) => {
//   input.addEventListener("change", validateAdherence);
// });

// function validateAdherence() {
//   const q1 = document.querySelector('input[name="question1"]:checked');
//   const q2 = document.querySelector('input[name="question2"]:checked');
//   const q3 = document.querySelector('input[name="question3"]:checked');
//   const q4 = document.querySelector('input[name="question4"]:checked');
//   const q5 = document.querySelector('input[name="question5"]:checked');
//   const errorMessage = document.getElementById("error-message");
//   const nextButton = document.getElementById("next-button");

//   let message = "";
//   let isValid = true;

//   if (q1 && q1.value === "Sim") {
//     message =
//       'Para esta declaração de acidente, clique <a href="https://declarante.prf.gov.br/declarante/" class="text-green-500 underline" target="_blank">aqui</a> para ser direcionado para o Sistema de Declaração de Acidente de Trânsito - DAT da PRF.';
//     isValid = false;
//   } else if (q2 && q2.value === "Não") {
//     message =
//       "Somente pessoas emancipadas ou maiores de 18 anos podem realizar a declaração do acidente.";
//     isValid = false;
//   } else if (
//     (q3 && q3.value === "Sim") ||
//     (q4 && q4.value === "Sim") ||
//     (q5 && q5.value === "Sim")
//   ) {
//     message =
//       "Este boletim não pode ser feito eletronicamente. Favor entrar em contato com Secretaria de Segurança Pública, Defesa Civil, Mobilidade Urbana e Trânsito - SESDEM.";
//     isValid = false;
//   } else if (!(q1 && q2 && q3 && q4 && q5)) {
//     message = "Por favor, responda a todas as perguntas.";
//     isValid = false;
//   } else {
//     message = "";
//     isValid = true;
//   }

//   if (isValid) {
//     errorMessage.classList.add("hidden");
//     nextButton.disabled = false;
//   } else {
//     errorMessage.innerHTML = `
//         <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
//           <span class="block sm:inline">${message}</span>
//         </div>`;
//     errorMessage.classList.remove("hidden");
//     nextButton.disabled = true;
//   }
// }

// document.getElementById("estrangeiro").addEventListener("change", function () {
//   const estrangeiroInfo = document.getElementById("estrangeiro-info");
//   if (this.checked) {
//     estrangeiroInfo.classList.remove("hidden");
//     document.getElementById("tipo-documento").setAttribute("required", "true");
//     document
//       .getElementById("numero-documento")
//       .setAttribute("required", "true");
//     document.getElementById("pais-documento").setAttribute("required", "true");
//   } else {
//     estrangeiroInfo.classList.add("hidden");
//     document.getElementById("tipo-documento").removeAttribute("required");
//     document.getElementById("numero-documento").removeAttribute("required");
//     document.getElementById("pais-documento").removeAttribute("required");
//   }
// });

// // Condutor: Campos relacionados à CNH
// document
//   .getElementById("nao-habilitado")
//   .addEventListener("change", function () {
//     const condutorInfo = document.getElementById("condutor-info");
//     if (this.checked) {
//       condutorInfo.classList.add("hidden");
//       document
//         .querySelectorAll("#condutor-info input, #condutor-info select")
//         .forEach((el) => {
//           el.removeAttribute("required");
//         });
//     } else {
//       condutorInfo.classList.remove("hidden");
//       document
//         .querySelectorAll("#condutor-info input, #condutor-info select")
//         .forEach((el) => {
//           el.setAttribute("required", "true");
//         });
//     }
//   });

// // Condutor: Campos relacionados ao Estrangeiro
// document
//   .getElementById("estrangeiro-condutor")
//   .addEventListener("change", function () {
//     const estrangeiroInfoCondutor = document.getElementById(
//       "estrangeiro-info-condutor"
//     );
//     if (this.checked) {
//       estrangeiroInfoCondutor.classList.remove("hidden");
//       document
//         .querySelectorAll(
//           "#estrangeiro-info-condutor input, #estrangeiro-info-condutor select"
//         )
//         .forEach((el) => {
//           el.setAttribute("required", "true");
//         });
//     } else {
//       estrangeiroInfoCondutor.classList.add("hidden");
//       document
//         .querySelectorAll(
//           "#estrangeiro-info-condutor input, #estrangeiro-info-condutor select"
//         )
//         .forEach((el) => {
//           el.removeAttribute("required");
//         });
//     }
//   });

// // Danos no Veículo: Partes danificadas
// document
//   .getElementById("danos-sistema-seguranca")
//   .addEventListener("change", function () {
//     const danosPartes = document.getElementById("danos-partes");
//     if (this.checked) {
//       danosPartes.classList.remove("hidden");
//     } else {
//       danosPartes.classList.add("hidden");
//     }
//   });

// // Danos na Carga: Campos relacionados à carga
// document.getElementById("danos-carga").addEventListener("change", function () {
//   const danosCargaInfo = document.getElementById("danos-carga-info");
//   if (this.checked) {
//     danosCargaInfo.classList.remove("hidden");
//     document.querySelectorAll("#danos-carga-info input").forEach((el) => {
//       el.setAttribute("required", "true");
//     });
//   } else {
//     danosCargaInfo.classList.add("hidden");
//     document.querySelectorAll("#danos-carga-info input").forEach((el) => {
//       el.removeAttribute("required");
//     });
//   }
// });

// // Danos na Carga: Seguradora
// document
//   .getElementById("tem-seguro-carga")
//   .addEventListener("change", function () {
//     const seguradoraCargaInfo = document.getElementById(
//       "seguradora-carga-info"
//     );
//     if (this.checked) {
//       seguradoraCargaInfo.classList.remove("hidden");
//       document
//         .getElementById("seguradora-carga")
//         .setAttribute("required", "true");
//     } else {
//       seguradoraCargaInfo.classList.add("hidden");
//       document.getElementById("seguradora-carga").removeAttribute("required");
//     }
//   });

// let vehicleCount = 0;

// function addVehicle() {
//   vehicleCount++;
//   const vehicleHTML = `
//       <div class="vehicle-form bg-gray-100 p-4 rounded-lg shadow-md mb-4">
//         <div class="flex justify-between items-center mb-4">
//           <h3 class="text-xl font-bold text-gray-700">Veículo ${vehicleCount}</h3>
//           <button type="button" class="toggle-vehicle-details text-green-500 underline">Expandir</button>
//         </div>
//         <div class="vehicle-details hidden">
//           <!-- Danos no veículo -->
//           <div class="mb-4">
//             <label class="flex items-center">
//               <input type="checkbox" class="mr-2 damage-checkbox"> Houve danos ao sistema de segurança, freios, direção ou de suspensão do veículo?
//             </label>
//             <div class="damage-parts hidden mt-2">
//               <label class="block text-gray-700 mb-2">Selecionar Partes Danificadas:</label>
//               <div class="grid grid-cols-2 gap-4">
//                 <label class="flex items-center">
//                   <input type="checkbox" class="mr-2"> Dianteira Direita
//                 </label>
//                 <label class="flex items-center">
//                   <input type="checkbox" class="mr-2"> Dianteira Esquerda
//                 </label>
//                 <label class="flex items-center">
//                   <input type="checkbox" class="mr-2"> Lateral/Teto Direito
//                 </label>
//                 <label class="flex items-center">
//                   <input type="checkbox" class="mr-2"> Lateral/Teto Esquerdo
//                 </label>
//                 <label class="flex items-center">
//                   <input type="checkbox" class="mr-2"> Traseira Direita
//                 </label>
//                 <label class="flex items-center">
//                   <input type="checkbox" class="mr-2"> Traseira Esquerda
//                 </label>
//               </div>
//             </div>
//           </div>

//           <!-- Danos na carga do veículo -->
//           <div class="mb-4">
//             <label class="flex items-center">
//               <input type="checkbox" class="mr-2 load-damage-checkbox"> Houve danos na carga do veículo?
//             </label>
//             <div class="load-damage-info hidden mt-2">
//               <div class="mb-4">
//                 <label class="block text-gray-700 mb-2">Nº das Notas Fiscais, Manifestos ou Equivalentes:</label>
//                 <input type="text" class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
//               </div>
//               <div class="mb-4">
//                 <label class="block text-gray-700 mb-2">Tipo de Mercadoria:</label>
//                 <input type="text" class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
//               </div>
//               <div class="mb-4">
//                 <label class="block text-gray-700 mb-2">Valor Total:</label>
//                 <input type="text" class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
//               </div>
//               <div class="mb-4">
//                 <label class="block text-gray-700 mb-2">Extensão estimada dos danos:</label>
//                 <input type="text" class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
//               </div>
//               <div class="mb-4">
//                 <label class="flex items-center">
//                   <input type="checkbox" class="mr-2 load-insurance-checkbox"> Tem seguro?
//                 </label>
//                 <div class="load-insurance-info hidden mt-2">
//                   <label class="block text-gray-700 mb-2">Informe a Seguradora:</label>
//                   <input type="text" class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
//                 </div>
//               </div>
//             </div>
//           </div>
//         </div>
//       </div>
//     `;

//   const container = document.getElementById("vehicle-container");
//   container.insertAdjacentHTML("beforeend", vehicleHTML);

//   // Adiciona interatividade para expandir e colapsar
//   const lastVehicleForm = container.lastElementChild;
//   const toggleBtn = lastVehicleForm.querySelector(".toggle-vehicle-details");
//   const detailsDiv = lastVehicleForm.querySelector(".vehicle-details");

//   toggleBtn.addEventListener("click", () => {
//     detailsDiv.classList.toggle("hidden");
//     toggleBtn.textContent = detailsDiv.classList.contains("hidden")
//       ? "Expandir"
//       : "Colapsar";
//   });

//   // Interatividade para o checkbox de danos no veículo
//   const damageCheckbox = lastVehicleForm.querySelector(".damage-checkbox");
//   const damagePartsDiv = lastVehicleForm.querySelector(".damage-parts");
//   damageCheckbox.addEventListener("change", () => {
//     damagePartsDiv.classList.toggle("hidden", !damageCheckbox.checked);
//   });

//   // Interatividade para o checkbox de danos na carga
//   const loadDamageCheckbox = lastVehicleForm.querySelector(
//     ".load-damage-checkbox"
//   );
//   const loadDamageInfoDiv = lastVehicleForm.querySelector(".load-damage-info");
//   loadDamageCheckbox.addEventListener("change", () => {
//     loadDamageInfoDiv.classList.toggle("hidden", !loadDamageCheckbox.checked);
//   });

//   // Interatividade para o checkbox de seguro da carga
//   const loadInsuranceCheckbox = lastVehicleForm.querySelector(
//     ".load-insurance-checkbox"
//   );
//   const loadInsuranceInfoDiv = lastVehicleForm.querySelector(
//     ".load-insurance-info"
//   );
//   loadInsuranceCheckbox.addEventListener("change", () => {
//     loadInsuranceInfoDiv.classList.toggle(
//       "hidden",
//       !loadInsuranceCheckbox.checked
//     );
//   });
// }

// // Adiciona um veículo quando o botão é clicado
// document
//   .getElementById("add-vehicle-btn")
//   .addEventListener("click", addVehicle);

// // Função para carregar a narrativa gerada

// // Funções para habilitar/contar caracteres nos campos de texto
// function toggleTextField(checkbox, textField, counter) {
//   checkbox.addEventListener("change", function () {
//     if (this.checked) {
//       textField.removeAttribute("disabled");
//       textField.classList.remove("bg-gray-200");
//       textField.classList.add("bg-white");
//     } else {
//       textField.setAttribute("disabled", "true");
//       textField.classList.remove("bg-white");
//       textField.classList.add("bg-gray-200");
//       textField.value = "";
//       counter.textContent = "0";
//     }
//   });

//   textField.addEventListener("input", function () {
//     counter.textContent = textField.value.length;
//   });
// }

// // Navegação entre etapas
// document.getElementById("anterior-btn").addEventListener("click", function () {
//   // Lógica para voltar à página anterior
//   // Exemplo: window.location.href = 'form3.html';
// });

// document.getElementById("proximo-btn").addEventListener("click", function () {
//   // Lógica para avançar à próxima página
//   // Exemplo: window.location.href = 'finalizacao.html';
// });

// const agreeCheckbox = document.getElementById("agree");
// const continueBtn = document.getElementById("continue-btn");

// agreeCheckbox.addEventListener("change", function () {
//   if (agreeCheckbox.checked) {
//     continueBtn.disabled = false;
//   } else {
//     continueBtn.disabled = true;
//   }
// });

// function nextStep(step) {
//   document.querySelectorAll(".step").forEach(function (stepDiv) {
//     stepDiv.classList.add("hidden");
//   });
//   document.getElementById("step-" + step).classList.remove("hidden");
//   window.scrollTo({
//     top: 0,
//     behavior: "smooth",
//   });

//   // Atualiza a barra de progresso
//   const progressBar = document.getElementById("progress-bar");
//   const width = (step - 1) * 16.6666667;
//   progressBar.style.width = `${width}%`;

//   if (step === 6) {
//     // Preencher os dados na tela de revisão
//     document.getElementById("review-nome").innerText =
//       document.getElementById("nome").value;
//     document.getElementById("review-email").innerText =
//       document.getElementById("email").value;

//     const selectedOptions = [];
//     document
//       .querySelectorAll('input[name="option"]:checked')
//       .forEach((option) => {
//         selectedOptions.push(option.parentElement.textContent.trim());
//       });
//     document.getElementById("review-opcoes").innerHTML = selectedOptions
//       .map((opt) => `<li>${opt}</li>`)
//       .join("");

//     const isEstrangeiro = document.getElementById("estrangeiro").checked
//       ? "Sim"
//       : "Não";
//     document.getElementById("review-estrangeiro").innerText = isEstrangeiro;
//   }
// }

// function submitForm() {
//   // Simulação de envio de formulário
//   const success = Math.random() > 0.2; // 80% de chance de sucesso
//   showModal(success);
// }

// function showModal(success) {
//   const modal = document.getElementById("modal");
//   const title = document.getElementById("modal-title");
//   const message = document.getElementById("modal-message");

//   if (success) {
//     title.innerText = "Sucesso!";
//     message.innerText = "Seu formulário foi enviado com sucesso.";
//   } else {
//     title.innerText = "Falha!";
//     message.innerText =
//       "Houve um erro ao enviar o formulário. Tente novamente.";
//   }

//   modal.classList.remove("hidden");
// }

// function closeModal() {
//   const modal = document.getElementById("modal");
//   modal.classList.add("hidden");
// }
