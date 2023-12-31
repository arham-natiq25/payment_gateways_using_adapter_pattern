<template>

    <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
    <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>

    <div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <b>
                        <label for="card-name">Cardholder's Name</label>
                    </b>
                    <input type="text" id="card-name" v-model="cardholderName" class="form-control " /> <br>
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <b>
                        <label for="email">Email</label>
                    </b>
                    <input type="email" id="email" v-model="email" class="form-control" /><br>

                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <b>
                        <label for="card-number">Card Number</label>
                    </b>
                    <div id="card-number" class="form-control card-input"></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <b>
                    <label for="card-expiry">Expiration Date</label>
                </b>
                <div id="card-expiry" class="form-control card-input"></div>
            </div>
            <div class="col-md-3">
                <b>
                    <label for="card-cvc">CVC</label>
                </b>
                <div id="card-cvc" class="form-control card-input"></div>
            </div>
        </div>
    </div>
    <!-- ON CLICK OF SUBMIT SOME TASKS PERFORMS -->
    <button class="btn btn-success mt-3" @click="submit">Submit</button>

    <div>
        <div class="row">
            <div class="col-md-8">
                <b><label>Select Card</label></b>

                <div v-for="card in cards" :key="card.id">
                    <input type="radio" :id="'card_' + card.id" name="selectedCard" class="m-2" :value="card" v-model="selectedCard">
                    <label :for="'card_' + card.id" class="m-2">
                        **** **** **** {{ card.last_four_digit }}
                    </label>
                </div>
            </div>

            <div class="col-md-3 mt-4">
                <button class="btn btn-success" @click="payWithCard">Pay</button>
            </div>
        </div>
    </div>

</template>
<script>
import axios from 'axios';
export default {
    name:"payment",
    data() {
        return {
            elements: null,
            stripe: null,
            cardNumberElement: null,
            cardExpiryElement: null,
            cardCvcElement: null,
            payment: 100,
            errorMessage: "",
            successMessage: "",
            cardholderName: "",
            email: "",
            user_id: null,
            cards:[],
            selectedCard:null,
        }
    },
    mounted() {
        this.loadStripe();
        this.getCustomerCards();
    },
    methods: {

        loadStripe() {
            // if current windonw has Stripe set primary key
            if (window.Stripe) {
                this.stripe = window.Stripe("pk_test_51NUU03Emu0Ala7lxKFLz0kgK8mfOVQr99wlJMIDW39xzneQ0B6Zb2x9irWjjNuldkUYyDFQG11FE50M6px3wvrVx00A0milkpo");
                this.elements = this.stripe.elements();
                // Create an instance of the card number Element
                this.cardNumberElement = this.elements.create("cardNumber", {
                    placeholder: "Card Number",
                });
                this.cardNumberElement.mount("#card-number");

                // Create an instance of the card expiry Element
                this.cardExpiryElement = this.elements.create("cardExpiry", {
                    placeholder: "MM / YY",
                });
                this.cardExpiryElement.mount("#card-expiry");

                // Create an instance of the card cvc Element
                this.cardCvcElement = this.elements.create("cardCvc", {
                    placeholder: "CVC",
                });
                this.cardCvcElement.mount("#card-cvc");
            } else {
                // Handle the case when Stripe is not available
                console.error("Stripe is not available");
            }
        },
        async submit() {
            this.errorMessage = "";
            const cardElement = this.elements.getElement("cardNumber");
            const { paymentMethod, error } = await this.stripe.createPaymentMethod({
                type: "card",
                card: cardElement,
                billing_details: {
                    name: this.cardholderName,
                    email: this.email,
                },
            });
            console.log("Error from Stripe:", error); // Log Stripe error
            if (error) {
                this.errorMessage = error.message;
            }
            else {
                this.processPayment(paymentMethod.id);
            }
            console.log("Final error message:", this.errorMessage); // Log final error message
        },
        async processPayment(paymentMethodId) {

            const attr = {
                    paymentMethodId,
                    payment: this.payment,
                    name: this.cardholderName,
                    email: this.email,
                    method:0
            }

            axios.post('/payment/pay',attr).then((response)=>{
                if (response.data.success) {
                    this.successMessage = response.data.message;
                setTimeout(() => {
                    this.successMessage = "";
                }, 10000);
                }else{
                    this.errorMessage=response.data.message
                }
            }).catch(error=>{
                    this.errorMessage=error.message;

                setTimeout(() => {
                this.errorMessage = "";
                }, 10000);
            });
        },
        getCustomerCards(){
            axios.get('/cards').then((res)=>{
                this.cards=res.data;
            });
        },
        payWithCard(){
        if (this.selectedCard==null) {
            this.errorMessage="Please Select Card"
        }else{
            this.errorMessage=""
            const attr = {
                card:this.selectedCard,
                method:1,
                payment:100
            }
            axios.post('/payment/pay',attr).then((res)=>{
                this.successMessage=res.data.message;
            }).catch(error=>{
                this.errorMessage=error;
            });
     }
    }

    },
}

</script>
